<?php

namespace Tests\Feature;

use App\Livewire\PreventiveMaintenanceForm;
use App\Models\Office;
use App\Models\PmChecklistItem;
use App\Models\PmRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PreventiveMaintenanceFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_position_and_start_date_are_required(): void
    {
        [$office] = $this->makeOfficeAndChecklist();

        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', '')
            ->set('position', '')
            ->set('date_started', '')
            ->call('save')
            ->assertHasErrors([
                'name' => 'required',
                'position' => 'required',
                'date_started' => 'required',
            ]);

        $this->assertDatabaseCount('pm_records', 0);
    }

    public function test_manual_position_is_preserved_when_status_selection_syncs_name(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();

        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('position', 'Technician')
            ->set('name', 'Jordan Lee')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->assertSet('position', 'Technician');
    }

    public function test_record_can_be_saved_without_remarks(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();

        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isSubmitted', true);

        $this->assertDatabaseHas('pm_records', [
            'office_id' => $office->id,
            'requested_by_name' => 'Jordan Lee',
            'position' => 'Technician',
            'date_started' => '2026-09-26 00:00:00',
        ]);
        $this->assertDatabaseHas('pm_record_items', [
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'good',
            'remarks' => null,
        ]);
    }

    public function test_incomplete_checklist_can_be_saved_and_updated_as_a_draft(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();

        $component = Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->call('saveAsDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('office-selection'));

        $draft = PmRecord::firstOrFail();
        $this->assertSame('draft', $draft->status);
        $this->assertDatabaseHas('pm_record_items', [
            'pm_record_id' => $draft->id,
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'pending',
        ]);

        $component = Livewire::test(PreventiveMaintenanceForm::class, ['record' => $draft->id])
            ->set('name', 'Jordan Lee Updated')
            ->set('position', 'Technician')
            ->call('saveAsDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('office-selection'));

        $this->assertDatabaseCount('pm_records', 1);
        $this->assertSame('Jordan Lee Updated', $draft->fresh()->requested_by_name);
        $this->assertSame('draft', $draft->fresh()->status);

        Livewire::test(PreventiveMaintenanceForm::class, ['record' => $draft->id])
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isSubmitted', true);

        $this->assertSame('pending', $draft->fresh()->status);
        $this->assertDatabaseCount('pm_records', 1);
    }

    public function test_saved_modal_conduct_again_starts_a_new_form(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();
        $component = Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->assertSet('confirmationAction', 'saved')
            ->assertSee('id="pm-form-confirmation"', false)
            ->assertSee('aria-hidden="false"', false)
            ->assertSee('Conduct Again')
            ->assertSee('Close')
            ->assertDontSee('Stay on Form')
            ->assertDontSee('Continue Editing')
            ->assertSet('isSubmitted', true);

        $component->call('confirmAction')
            ->assertSet('confirmationAction', null)
            ->assertSet('isSubmitted', false)
            ->assertSet('name', '')
            ->assertSet('position', '');

        $this->assertDatabaseCount('pm_records', 1);
    }

    public function test_saved_modal_close_returns_to_office_selection(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();
        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->call('cancelConfirmation')
            ->assertRedirect(route('office-selection'));
    }

    public function test_ram_specs_and_recommendation_are_saved_separately(): void
    {
        $office = Office::create([
            'name' => 'Guidance Office',
            'status' => 'active',
            'computer_count' => 5,
        ]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'System Unit',
            'task_name' => 'Check RAM capacity and condition',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'defective')
            ->set("itemRemarks.{$checklistItem->id}", '8GB DDR4')
            ->set("itemRecommendations.{$checklistItem->id}", 'Increase RAM Capacity')
            ->assertSee('>Remarks</label>', false)
            ->assertSee('Increase RAM Capacity')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pm_record_items', [
            'pm_checklist_item_id' => $checklistItem->id,
            'remarks' => '8GB DDR4',
            'recommendation' => 'Increase RAM Capacity',
        ]);
    }

    public function test_keyboard_and_mouse_findings_have_item_specific_remark_choices(): void
    {
        [$office] = $this->makeOfficeAndChecklist();
        $component = Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])->instance();

        $options = $component->recommendationOptions([
            'section' => 'Keyboard',
            'task_name' => 'Check for cable cuts or damages',
        ]);

        $this->assertSame([
            ['label' => 'Change Keyboard', 'value' => 'Change Keyboard'],
        ], $options);

        $dustTask = [
            'section' => 'Keyboard',
            'task_name' => 'Clean dust and other foreign object inside the keyboard',
        ];
        $this->assertSame([], $component->recommendationOptions($dustTask));
        $this->assertFalse($component->itemAllowsFreeformRemarks($dustTask));
        $this->assertContains(['label' => 'Done', 'value' => 'done'], $component->statusOptions($dustTask));

        $this->assertSame([
            ['label' => 'Change Mouse', 'value' => 'Change Mouse'],
        ], $component->recommendationOptions([
            'section' => 'Mouse',
            'task_name' => 'Check if mouse is responsive',
        ]));
    }

    public function test_status_dropdown_has_only_good_defective_and_done(): void
    {
        [$office] = $this->makeOfficeAndChecklist();
        $component = Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])->instance();

        $this->assertSame([
            ['label' => 'Good', 'value' => 'good'],
            ['label' => 'Defective', 'value' => 'defective'],
            ['label' => 'Done', 'value' => 'done'],
        ], $component->statusOptions(['section' => 'Hardware', 'task_name' => 'Inspect workstation']));
    }

    public function test_keyboard_cleaning_task_can_be_marked_done_without_remark_or_recommendation(): void
    {
        $office = Office::create(['name' => 'Guidance Office', 'status' => 'active', 'computer_count' => 1]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'Keyboard',
            'task_name' => 'Clean dust and other foreign object inside the keyboard',
            'finding_label' => 'Keyboard has dust or foreign objects inside',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'done')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pm_record_items', [
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'done',
            'remarks' => null,
            'recommendation' => null,
        ]);
    }

    public function test_return_to_office_selection_requires_confirmation(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();
        Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->call('requestOfficeSelectionConfirmation')
            ->assertSet('confirmationAction', 'office-selection')
            ->assertSee('Return to office selection?')
            ->call('confirmAction')
            ->assertRedirect(route('office-selection'));
    }

    private function makeOfficeAndChecklist(): array
    {
        $office = Office::create([
            'name' => 'Guidance Office',
            'status' => 'active',
            'computer_count' => 5,
        ]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'Hardware',
            'task_name' => 'Inspect workstation',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return [$office, $checklistItem];
    }
}
