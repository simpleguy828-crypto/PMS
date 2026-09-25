<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\PmChecklistItem;
use App\Models\PmRecord;
use App\Models\PmRecordItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class PreventiveMaintenanceForm extends Component
{
    public $office_id;
    public $name = '';
    public $position = '';
    public $date_started = '';

    public $checklistItems = [];
    public $itemStatus = []; // item_id => string (good|defective|na|needs_attention)
    public $itemRemarks = []; // item_id => string
    public $itemDateCompleted = []; // item_id => string (date)
    public $itemDateEdited = []; // item_id => boolean (true if date completed manually edited)

    public $users = [];

    // Submission state tracking
    public $isSubmitted = false;
    public $savedPmRecordId = null;

    // Edit mode: true when opened via the "Edit" button on an existing record
    public $isEditMode = false;
    public $editingRecordId = null;

    // For displaying department information (office and department are the same value)
    public $selectedDepartmentInfo = '';
    public $accordionStates = []; // section => boolean (open/closed)

    /**
     * $officeId  -> used by the normal "conduct new PM" flow (from office-selection)
     * $record    -> used by the "edit existing record" flow (from pm-records-list Edit button)
     *               Route parameter must be named {record} to bind here automatically.
     */
    public function mount($officeId = null, $record = null)
    {
        $this->loadUsers();
        $this->loadChecklistItems();

        if ($record) {
            $this->loadForEdit($record);
            return;
        }

        $this->date_started = Carbon::today()->toDateString();

        // Initialize accordion states: all sections closed by default
        $sections = collect($this->checklistItems)->pluck('section')->unique();
        foreach ($sections as $section) {
            $this->accordionStates[$section] = false;
        }

        // If office_id is provided (from OfficeSelection screen), set it and load office details
        if ($officeId) {
            $this->office_id = $officeId;
            $this->loadSelectedOfficeDetails($officeId);
        }
    }

    /**
     * Pre-fill every field from an existing PmRecord so it can be corrected
     * and re-saved. Only checklist items that are still active are shown;
     * any saved values for since-deactivated items are simply not displayed.
     */
    protected function loadForEdit($recordId)
    {
        $pmRecord = PmRecord::with(['office', 'recordItems'])->findOrFail($recordId);

        $this->isEditMode = true;
        $this->editingRecordId = $pmRecord->id;

        $this->office_id = $pmRecord->office_id;
        $this->name = $pmRecord->requested_by_name;
        $this->position = $pmRecord->position;
        $this->date_started = optional($pmRecord->date_started)->format('Y-m-d') ?? $pmRecord->date_started;
        $this->selectedDepartmentInfo = $pmRecord->office->name ?? '';

        // Map existing record items by checklist_item_id for quick lookup
        $existingItems = $pmRecord->recordItems->keyBy('pm_checklist_item_id');

        foreach ($this->checklistItems as $item) {
            $itemId = $item['id'];
            $existing = $existingItems->get($itemId);

            if ($existing) {
                $this->itemStatus[$itemId] = $existing->status;
                $this->itemRemarks[$itemId] = $existing->remarks ?? '';
                $this->itemDateCompleted[$itemId] = optional($existing->date_completed)->format('Y-m-d')
                    ?? $existing->date_completed
                    ?? '';
                $this->itemDateEdited[$itemId] = true; // preserve saved dates as-is; don't auto-overwrite on date_started change
            } else {
                $this->itemStatus[$itemId] = null;
                $this->itemRemarks[$itemId] = '';
                $this->itemDateCompleted[$itemId] = $this->date_started;
                $this->itemDateEdited[$itemId] = false;
            }
        }

        // Expand all sections by default in edit mode so the user sees everything at once
        $sections = collect($this->checklistItems)->pluck('section')->unique();
        foreach ($sections as $section) {
            $this->accordionStates[$section] = true;
        }
    }

    public function loadUsers()
    {
        $this->users = User::select('name', 'position')->get()->toArray();
    }

    public function loadChecklistItems()
    {
        $this->checklistItems = PmChecklistItem::where('is_active', true)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'section' => $item->section,
                    'task_name' => $item->task_name,
                    'finding_label' => $item->finding_label,
                ];
            })->toArray();

        // Initialize status, remarks, date completed, and date edited flag for each item.
        // (Skipped when loading for edit — loadForEdit() fills these from the saved record instead.)
        if (!$this->isEditMode) {
            foreach ($this->checklistItems as $item) {
                $this->itemStatus[$item['id']] = null;
                $this->itemRemarks[$item['id']] = '';
                $this->itemDateCompleted[$item['id']] = $this->date_started;
                $this->itemDateEdited[$item['id']] = false;
            }
        }
    }

    public function loadSelectedOfficeDetails($officeId)
    {
        $office = Office::find($officeId);
        if ($office) {
            $this->selectedDepartmentInfo = $office->name;
        }
    }

    public function updatedOfficeId($officeId)
    {
        $office = Office::find($officeId);
        if ($office) {
            $this->selectedDepartmentInfo = $office->name;
        } else {
            $this->selectedDepartmentInfo = '';
        }
    }

    public function updatedName($name)
    {
        // Auto-fill position if exact case-insensitive match
        $match = collect($this->users)->first(function ($user) use ($name) {
            return strtolower($user['name']) === strtolower($name);
        });

        if ($match) {
            $this->position = $match['position'];
        } else {
            // If no match, clear the position field (it becomes empty and editable)
            $this->position = '';
        }
    }

    public function updatedDateStarted($date)
    {
        // Auto-fill date completed for tasks that have not been manually edited
        foreach ($this->checklistItems as $item) {
            $itemId = $item['id'];
            if (!$this->itemDateEdited[$itemId]) {
                $this->itemDateCompleted[$itemId] = $date;
            }
        }
    }

    /**
     * Reserved Livewire lifecycle hook for the nested/array property `itemStatus`.
     * Livewire calls this AUTOMATICALLY whenever wire:model="itemStatus.{id}" changes —
     * it must never be invoked directly from a wire:click/wire:change/wire:input in the view.
     * Livewire's convention for array properties is ($value, $key), value first.
     */
    public function updatedItemStatus($status, $itemId)
    {
        $this->itemStatus[$itemId] = $status;

        // Auto-fill date completed if not manually edited and status is set
        if (!$this->itemDateEdited[$itemId] && in_array($status, ['good', 'defective', 'na', 'needs_attention'])) {
            $this->itemDateCompleted[$itemId] = $this->date_started;
        }
        // If status is cleared, clear date completed if not manually edited
        if (empty($status) && !$this->itemDateEdited[$itemId]) {
            $this->itemDateCompleted[$itemId] = '';
        }
    }

    /**
     * Reserved Livewire lifecycle hook for `itemRemarks`. Fires automatically from
     * wire:model.live="itemRemarks.{id}" — do not call directly from the view.
     */
    public function updatedItemRemarks($remarks, $itemId)
    {
        $this->itemRemarks[$itemId] = $remarks;
        // If status is defective and remarks now filled, remove highlight (handled in view)
    }

    /**
     * Reserved Livewire lifecycle hook for `itemDateCompleted`. Fires automatically from
     * wire:model.live="itemDateCompleted.{id}" — do not call directly from the view.
     */
    public function updatedItemDateCompleted($date, $itemId)
    {
        // Mark this date as manually edited
        $this->itemDateEdited[$itemId] = true;
    }

    public function toggleAccordion($section)
    {
        $this->accordionStates[$section] = !$this->accordionStates[$section];
    }

    public function setItemStatus($itemId, $status)
    {
        $this->itemStatus[$itemId] = $status;
        // Auto-fill date completed if not manually edited and status is set
        if (!$this->itemDateEdited[$itemId] && in_array($status, ['good', 'defective', 'na', 'needs_attention'])) {
            $this->itemDateCompleted[$itemId] = $this->date_started;
        }
        // If status is cleared, clear date completed if not manually edited
        if (empty($status) && !$this->itemDateEdited[$itemId]) {
            $this->itemDateCompleted[$itemId] = '';
        }
    }

    public function editItemDateCompleted($itemId)
    {
        // Allow manual editing of date completed
        $this->itemDateEdited[$itemId] = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditMode) {
            $this->updateExistingRecord();
            return;
        }

        // Save PM record
        // Note: department is NOT stored here — pm_records has no `department`
        // column. Department is derived from the office relationship instead
        // (see $record->office->name in PmRecordsList and the PDF template).
        $pmRecord = PmRecord::create([
            'office_id' => $this->office_id,
            'requested_by_name' => $this->name,
            'position' => $this->position,
            'date_started' => $this->date_started,
        ]);

        // Save PM record items
        foreach ($this->checklistItems as $item) {
            $itemId = $item['id'];
            PmRecordItem::create([
                'pm_record_id' => $pmRecord->id,
                'pm_checklist_item_id' => $itemId,
                'status' => $this->itemStatus[$itemId],
                'date_completed' => $this->itemDateCompleted[$itemId],
                'remarks' => $this->itemRemarks[$itemId],
            ]);
        }

        // Set submission state
        $this->isSubmitted = true;
        $this->savedPmRecordId = $pmRecord->id;

        // Show success message
        session()->flash('message', 'Preventive maintenance record has been saved.');
    }

    /**
     * Update the existing PmRecord and its checklist items instead of
     * creating a new record. Used only when opened via the Edit button.
     */
    protected function updateExistingRecord()
    {
        $pmRecord = PmRecord::with('recordItems')->findOrFail($this->editingRecordId);

        $pmRecord->update([
            'office_id' => $this->office_id,
            'requested_by_name' => $this->name,
            'position' => $this->position,
            'date_started' => $this->date_started,
        ]);

        $existingItems = $pmRecord->recordItems->keyBy('pm_checklist_item_id');

        foreach ($this->checklistItems as $item) {
            $itemId = $item['id'];
            $existing = $existingItems->get($itemId);

            $data = [
                'status' => $this->itemStatus[$itemId],
                'date_completed' => $this->itemDateCompleted[$itemId] ?: null,
                'remarks' => $this->itemRemarks[$itemId],
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                // A checklist item was added after this record was originally saved.
                PmRecordItem::create(array_merge($data, [
                    'pm_record_id' => $pmRecord->id,
                    'pm_checklist_item_id' => $itemId,
                ]));
            }
        }

        session()->flash('message', 'Record #' . $pmRecord->id . ' has been updated.');
    }

    public function conductAgain()
    {
        // Reset form for a new record in the same office
        $this->reset([
            'name',
            'position',
            'date_started',
            'itemStatus',
            'itemRemarks',
            'itemDateCompleted',
            'itemDateEdited'
        ]);

        // Keep office_id and department information locked in
        $this->date_started = Carbon::today()->toDateString();
        $this->isSubmitted = false;
        $this->savedPmRecordId = null;

        // Reinitialize checklist items
        $this->loadChecklistItems();

        // Reinitialize accordion states: all sections closed by default
        $sections = collect($this->checklistItems)->pluck('section')->unique();
        foreach ($sections as $section) {
            $this->accordionStates[$section] = false;
        }
    }

    public function backToOfficeSelection()
    {
        // Redirect to office selection screen
        return redirect()->route('office-selection');
    }

    public function backToRecordsList()
    {
        return redirect()->route('pm-records-list');
    }

    public function downloadPdf()
    {
        $recordId = $this->isEditMode ? $this->editingRecordId : $this->savedPmRecordId;

        if (!$recordId) {
            session()->flash('error', 'No record to download. Please save a record first.');
            return;
        }

        return redirect()->route('pm-record.pdf', ['id' => $recordId]);
    }

    public function downloadDocx()
    {
        $recordId = $this->isEditMode ? $this->editingRecordId : $this->savedPmRecordId;

        if (!$recordId) {
            session()->flash('error', 'No record to download. Please save a record first.');
            return;
        }

        return redirect()->route('pm-record.docx', ['id' => $recordId]);
    }

    protected function rules()
    {
        $rules = [
            'office_id' => 'required|exists:offices,id',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'date_started' => 'required|date',
        ];

        // Dynamically add rules for checklist items
        foreach ($this->checklistItems as $item) {
            $itemId = $item['id'];
            $rules["itemStatus.$itemId"] = 'required|in:good,defective,na,needs_attention';
            $rules["itemRemarks.$itemId"] = 'nullable|string|max:500';
            $rules["itemDateCompleted.$itemId"] = 'nullable|date';
        }

        return $rules;
    }

    public function computeFormReady()
    {
        // Check basic fields
        if (empty($this->office_id) || empty($this->name) || empty($this->position) || empty($this->date_started)) {
            return false;
        }

        // Check that all checklist items have a status set (good, defective, na, or needs_attention)
        foreach ($this->checklistItems as $item) {
            $itemId = $item['id'];
            if (!isset($this->itemStatus[$itemId]) ||
                !in_array($this->itemStatus[$itemId], ['good', 'defective', 'na', 'needs_attention'])) {
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        $offices = Office::all();
        $formReady = $this->computeFormReady();
        return view('livewire.preventive-maintenance-form', compact('offices'))
            ->with([
                'formReady' => $formReady,
            ]);
    }
}