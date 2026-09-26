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
            'remarks' => '',
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
            ->assertSee('Save as Draft')
            ->assertSet('isEditMode', true);

        $draft = PmRecord::firstOrFail();
        $this->assertSame('draft', $draft->status);
        $this->assertDatabaseHas('pm_record_items', [
            'pm_record_id' => $draft->id,
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'pending',
        ]);

        $component->set('name', 'Jordan Lee Updated')
            ->set('position', 'Technician')
            ->call('saveAsDraft')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('pm_records', 1);
        $this->assertSame('Jordan Lee Updated', $draft->fresh()->requested_by_name);
        $this->assertSame('draft', $draft->fresh()->status);

        $component->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isSubmitted', true);

        $this->assertSame('pending', $draft->fresh()->status);
        $this->assertDatabaseCount('pm_records', 1);
    }

    public function test_conduct_again_requires_confirmation_before_resetting_the_form(): void
    {
        [$office, $checklistItem] = $this->makeOfficeAndChecklist();
        $component = Livewire::test(PreventiveMaintenanceForm::class, ['officeId' => $office->id])
            ->set('name', 'Jordan Lee')
            ->set('position', 'Technician')
            ->set('date_started', '2026-09-26')
            ->set("itemStatus.{$checklistItem->id}", 'good')
            ->call('save')
            ->call('requestConductAgainConfirmation')
            ->assertSet('confirmationAction', 'conduct-again')
            ->assertSee('id="pm-form-confirmation"', false)
            ->assertSee('aria-hidden="false"', false)
            ->assertSee('Conduct another maintenance?')
            ->assertSet('isSubmitted', true);

        $component->call('confirmAction')
            ->assertSet('confirmationAction', null)
            ->assertSet('isSubmitted', false)
            ->assertSet('name', '')
            ->assertSet('position', '');

        $this->assertDatabaseCount('pm_records', 1);
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
