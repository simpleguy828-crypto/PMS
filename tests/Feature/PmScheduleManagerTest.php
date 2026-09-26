<?php

namespace Tests\Feature;

use App\Livewire\PmScheduleManager;
use App\Models\Office;
use App\Models\PmRecord;
use App\Models\PmSchedule;
use App\Models\PmScheduleOffice;
use App\Models\PmScheduleReschedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PmScheduleManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_creation_and_editing_syncs_office_assignments(): void
    {
        $firstOffice = Office::create([
            'name' => 'Guidance Office',
            'status' => 'active',
            'computer_count' => 2,
        ]);
        $secondOffice = Office::create([
            'name' => 'MIS Office',
            'status' => 'active',
            'computer_count' => 4,
        ]);

        Livewire::test(PmScheduleManager::class)
            ->set('scheduledDate', '2026-10-15')
            ->set('selectedOfficeIds', [$firstOffice->id])
            ->set('notes', 'Quarterly batch')
            ->call('saveSchedule')
            ->assertHasNoErrors();

        $schedule = PmSchedule::firstOrFail();
        $this->assertSame('2026-10-15', $schedule->scheduled_date->toDateString());
        $this->assertSame('Quarterly batch', $schedule->notes);
        $this->assertDatabaseHas('pm_schedule_offices', [
            'pm_schedule_id' => $schedule->id,
            'office_id' => $firstOffice->id,
        ]);

        Livewire::test(PmScheduleManager::class)
            ->call('editSchedule', $schedule->id)
            ->set('scheduledDate', '2026-10-20')
            ->set('selectedOfficeIds', [$secondOffice->id])
            ->set('notes', 'Updated batch')
            ->call('saveSchedule')
            ->assertHasNoErrors();

        $this->assertSame('2026-10-20', $schedule->fresh()->scheduled_date->toDateString());
        $this->assertSame('Updated batch', $schedule->fresh()->notes);
        $this->assertDatabaseMissing('pm_schedule_offices', [
            'pm_schedule_id' => $schedule->id,
            'office_id' => $firstOffice->id,
        ]);
        $this->assertDatabaseHas('pm_schedule_offices', [
            'pm_schedule_id' => $schedule->id,
            'office_id' => $secondOffice->id,
            'current_scheduled_date' => '2026-10-20 00:00:00',
        ]);
    }

    public function test_later_schedule_records_do_not_complete_an_earlier_schedule(): void
    {
        $office = Office::create([
            'name' => 'Guidance Office',
            'status' => 'active',
            'computer_count' => 2,
        ]);
        $first = PmSchedule::create(['scheduled_date' => '2026-10-01']);
        $second = PmSchedule::create(['scheduled_date' => '2026-10-15']);
        PmScheduleOffice::create([
            'pm_schedule_id' => $first->id,
            'office_id' => $office->id,
            'current_scheduled_date' => '2026-10-01',
        ]);
        PmScheduleOffice::create([
            'pm_schedule_id' => $second->id,
            'office_id' => $office->id,
            'current_scheduled_date' => '2026-10-15',
        ]);
        PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Tester',
            'position' => 'Staff',
            'date_started' => '2026-10-20',
            'status' => 'completed',
        ]);

        Livewire::test(PmScheduleManager::class)
            ->set('monthFilter', '2026-10')
            ->assertSee('Not Started')
            ->assertSee('Partially Completed — 1 of 2 checked');
    }

    public function test_reschedule_updates_current_date_and_writes_audit_row(): void
    {
        $office = Office::create([
            'name' => 'MIS Office',
            'status' => 'active',
            'computer_count' => 3,
        ]);
        $schedule = PmSchedule::create(['scheduled_date' => '2026-10-15']);
        $scheduleOffice = PmScheduleOffice::create([
            'pm_schedule_id' => $schedule->id,
            'office_id' => $office->id,
            'current_scheduled_date' => '2026-10-15',
        ]);

        Livewire::test(PmScheduleManager::class)
            ->call('beginReschedule', $scheduleOffice->id)
            ->set('rescheduleDate', '2026-10-22')
            ->call('saveSchedule');

        $this->assertSame(
            '2026-10-22',
            PmScheduleOffice::findOrFail($scheduleOffice->id)->current_scheduled_date->toDateString()
        );
        $audit = PmScheduleReschedule::where('pm_schedule_office_id', $scheduleOffice->id)->firstOrFail();
        $this->assertSame('2026-10-15', $audit->old_date->toDateString());
        $this->assertSame('2026-10-22', $audit->new_date->toDateString());
    }

    public function test_completed_office_schedules_cannot_be_rescheduled(): void
    {
        $office = Office::create([
            'name' => 'Registrar Office',
            'status' => 'active',
            'computer_count' => 1,
        ]);
        $schedule = PmSchedule::create(['scheduled_date' => '2026-10-15']);
        $scheduleOffice = PmScheduleOffice::create([
            'pm_schedule_id' => $schedule->id,
            'office_id' => $office->id,
            'current_scheduled_date' => '2026-10-15',
        ]);
        PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Tester',
            'position' => 'Staff',
            'date_started' => '2026-10-16',
            'status' => 'completed',
        ]);

        Livewire::test(PmScheduleManager::class)
            ->set('monthFilter', '2026-10')
            ->assertSee('Completed')
            ->assertDontSee('>Reschedule</button>')
            ->call('beginReschedule', $scheduleOffice->id)
            ->assertSet('open', false);
    }
}
