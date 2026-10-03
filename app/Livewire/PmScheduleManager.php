<?php

namespace App\Livewire;

use App\Livewire\Concerns\NavigatesPages;
use App\Models\Office;
use App\Models\PmRecord;
use App\Models\PmSchedule;
use App\Models\PmScheduleOffice;
use Illuminate\Support\Carbon;
use Livewire\Component;

class PmScheduleManager extends Component
{
    use NavigatesPages;

    public $open = false;
    public $drawerMode = 'create';
    public $scheduledDate = '';
    public $notes = '';
    public $selectedOfficeIds = [];
    public $editingScheduleId = null;
    public $reschedulingOfficeId = null;
    public $rescheduleDate = '';
    public $monthFilter = '';
    public $officeSearch = '';
    public $officeSortOrder = 'name_asc';

    protected function rules()
    {
        if ($this->drawerMode === 'reschedule') {
            return ['rescheduleDate' => 'required|date'];
        }

        return [
            'scheduledDate' => 'required|date',
            'notes' => 'nullable|string',
            'selectedOfficeIds' => 'required|array|min:1',
            'selectedOfficeIds.*' => 'integer|exists:offices,id',
        ];
    }

    public function mount()
    {
        $this->scheduledDate = now()->toDateString();
        $this->monthFilter = now()->format('Y-m');
    }

    public function render()
    {
        $start = Carbon::createFromFormat('Y-m', $this->monthFilter ?: now()->format('Y-m'))->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $schedules = PmSchedule::with(['scheduleOffices.office'])
            ->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('scheduled_date')
            ->orderBy('id')
            ->get();

        foreach ($schedules as $schedule) {
            foreach ($schedule->scheduleOffices as $scheduleOffice) {
                $count = $this->completionCount($scheduleOffice);
                $scheduleOffice->completion_count = $count;
                $scheduleOffice->completion_status = $this->completionStatus(
                    $count,
                    (int) ($scheduleOffice->office->computer_count ?? 0)
                );
            }

            $scheduleOffices = $schedule->scheduleOffices;
            if (trim($this->officeSearch) !== '') {
                $search = mb_strtolower(trim($this->officeSearch));
                $scheduleOffices = $scheduleOffices->filter(fn ($scheduleOffice) => str_contains(
                    mb_strtolower($scheduleOffice->office->name),
                    $search
                ));
            }

            $scheduleOffices = $this->officeSortOrder === 'name_desc'
                ? $scheduleOffices->sortByDesc(fn ($scheduleOffice) => mb_strtolower($scheduleOffice->office->name))
                : $scheduleOffices->sortBy(fn ($scheduleOffice) => mb_strtolower($scheduleOffice->office->name));
            $schedule->setRelation('scheduleOffices', $scheduleOffices->values());
        }

        return view('livewire.pm-schedule-manager', [
            'schedules' => $schedules,
            'offices' => $this->availableOffices(),
            'drawerTitle' => $this->drawerTitle(),
            'officeSortOptions' => [
                ['label' => 'Office name: A to Z', 'value' => 'name_asc'],
                ['label' => 'Office name: Z to A', 'value' => 'name_desc'],
            ],
            'reschedulingOffice' => $this->reschedulingOfficeId
                ? PmScheduleOffice::with(['office', 'schedule'])->find($this->reschedulingOfficeId)
                : null,
        ]);
    }

    public function updatedOfficeSortOrder($value)
    {
        if (!in_array($value, ['name_asc', 'name_desc'], true)) {
            $this->officeSortOrder = 'name_asc';
        }
    }

    public function createSchedule()
    {
        $this->resetDrawer();
        $this->drawerMode = 'create';
        $this->open = true;
    }

    public function editSchedule($scheduleId)
    {
        $schedule = PmSchedule::with('scheduleOffices.reschedules')->findOrFail($scheduleId);
        $this->resetDrawer();
        $this->drawerMode = 'edit';
        $this->editingScheduleId = $schedule->id;
        $this->scheduledDate = $schedule->scheduled_date->toDateString();
        $this->notes = $schedule->notes ?? '';
        $this->selectedOfficeIds = $schedule->scheduleOffices->pluck('office_id')->all();
        $this->open = true;
    }

    public function beginReschedule($scheduleOfficeId)
    {
        $scheduleOffice = PmScheduleOffice::with(['office', 'schedule'])->findOrFail($scheduleOfficeId);
        if ($this->isCompleted($scheduleOffice)) {
            return;
        }

        $this->resetDrawer();
        $this->drawerMode = 'reschedule';
        $this->reschedulingOfficeId = $scheduleOffice->id;
        $this->rescheduleDate = $scheduleOffice->current_scheduled_date->toDateString();
        $this->open = true;
    }

    public function saveSchedule()
    {
        $this->validate();

        if ($this->drawerMode === 'reschedule') {
            return $this->saveReschedule();
        }

        $isEditing = $this->drawerMode === 'edit';
        $schedule = $isEditing
            ? PmSchedule::with('scheduleOffices.reschedules')->findOrFail($this->editingScheduleId)
            : new PmSchedule();
        $schedule->scheduled_date = $this->scheduledDate;
        $schedule->notes = $this->notes ?: null;
        $schedule->save();

        $selectedIds = collect($this->selectedOfficeIds)->map(fn ($id) => (int) $id)->all();
        $existing = $schedule->scheduleOffices->keyBy('office_id');
        foreach ($existing as $officeId => $scheduleOffice) {
            if (! in_array((int) $officeId, $selectedIds, true)) {
                $scheduleOffice->delete();
                continue;
            }

            if ($scheduleOffice->reschedules->isEmpty()) {
                $scheduleOffice->current_scheduled_date = $this->scheduledDate;
                $scheduleOffice->save();
            }
        }

        $existingIds = $existing->keys()->map(fn ($id) => (int) $id)->all();
        foreach (array_diff($selectedIds, $existingIds) as $officeId) {
            $schedule->scheduleOffices()->create([
                'office_id' => $officeId,
                'current_scheduled_date' => $this->scheduledDate,
            ]);
        }

        session()->flash('message', $isEditing ? 'Schedule updated.' : 'Schedule created.');
        $this->closeModal();
    }

    protected function saveReschedule()
    {
        $scheduleOffice = PmScheduleOffice::with('office')->findOrFail($this->reschedulingOfficeId);
        if ($this->isCompleted($scheduleOffice)) {
            $this->addError('rescheduleDate', 'This office schedule is already completed.');
            return;
        }

        $oldDate = $scheduleOffice->current_scheduled_date->toDateString();
        $scheduleOffice->reschedules()->create([
            'old_date' => $oldDate,
            'new_date' => $this->rescheduleDate,
            'rescheduled_at' => now(),
        ]);
        $scheduleOffice->current_scheduled_date = $this->rescheduleDate;
        $scheduleOffice->save();

        session()->flash('message', 'Office schedule rescheduled.');
        $this->closeModal();
    }

    public function removeOffice($scheduleOfficeId)
    {
        PmScheduleOffice::findOrFail($scheduleOfficeId)->delete();
        session()->flash('message', 'Office removed from schedule.');
    }

    public function deleteSchedule($scheduleId)
    {
        PmSchedule::findOrFail($scheduleId)->delete();
        session()->flash('message', 'Schedule deleted.');
    }

    public function toggleSelectAll()
    {
        $officeIds = $this->availableOffices()->pluck('id')->all();
        $selectedIds = array_map('intval', $this->selectedOfficeIds);
        $this->selectedOfficeIds = count($officeIds) === count(array_intersect($officeIds, $selectedIds))
            ? []
            : $officeIds;
    }

    public function closeModal()
    {
        $this->open = false;
        $this->resetDrawer();
    }

    protected function completionCount(PmScheduleOffice $scheduleOffice)
    {
        $nextDate = PmScheduleOffice::where('office_id', $scheduleOffice->office_id)
            ->whereDate('current_scheduled_date', '>', $scheduleOffice->current_scheduled_date)
            ->orderBy('current_scheduled_date')
            ->value('current_scheduled_date');
        $query = PmRecord::where('office_id', $scheduleOffice->office_id)
            ->whereDate('date_started', '>=', $scheduleOffice->current_scheduled_date);

        if ($nextDate) {
            $query->whereDate('date_started', '<', $nextDate);
        }

        return $query->count();
    }

    protected function isCompleted(PmScheduleOffice $scheduleOffice)
    {
        return $this->completionStatus(
            $this->completionCount($scheduleOffice),
            (int) $scheduleOffice->office->computer_count
        ) === 'Completed';
    }

    protected function completionStatus($count, $computerCount)
    {
        if ($count === 0) {
            return 'Not Started';
        }

        if ($count < $computerCount) {
            return "Partially Completed — {$count} of {$computerCount} checked";
        }

        return 'Completed';
    }

    protected function availableOffices()
    {
        return Office::where('status', 'active')
            ->orWhereHas('scheduleOffices', fn ($query) => $query->where('pm_schedule_id', $this->editingScheduleId))
            ->orderBy('name')
            ->get();
    }

    protected function drawerTitle()
    {
        if ($this->drawerMode === 'reschedule') {
            return 'Reschedule Office';
        }

        return $this->drawerMode === 'edit' ? 'Edit Schedule' : 'New Schedule';
    }

    protected function resetDrawer()
    {
        $this->resetValidation();
        $this->drawerMode = 'create';
        $this->scheduledDate = now()->toDateString();
        $this->notes = '';
        $this->selectedOfficeIds = [];
        $this->editingScheduleId = null;
        $this->reschedulingOfficeId = null;
        $this->rescheduleDate = '';
    }
}