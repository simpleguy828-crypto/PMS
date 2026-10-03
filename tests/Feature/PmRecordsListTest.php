<?php

namespace Tests\Feature;

use App\Livewire\PmRecordsList;
use App\Models\Office;
use App\Models\PmChecklistItem;
use App\Models\PmRecord;
use App\Models\PmRecordItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PmRecordsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_can_be_searched_and_sorted_by_date_in_both_directions(): void
    {
        $office = Office::create([
            'name' => 'MIS Office',
            'status' => 'active',
            'computer_count' => 2,
        ]);
        PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Earlier record',
            'position' => 'Technician',
            'date_started' => '2026-09-20',
            'status' => 'pending',
        ]);
        PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Later record',
            'position' => 'Technician',
            'date_started' => '2026-09-25',
            'status' => 'pending',
        ]);

        $component = Livewire::test(PmRecordsList::class)
            ->assertSee('Date/time: newest first')
            ->assertSeeInOrder(['Later record', 'Earlier record']);

        $component->set('sortOrder', 'date_asc')
            ->assertSeeInOrder(['Earlier record', 'Later record'])
            ->set('sortOrder', 'name_desc')
            ->assertSeeInOrder(['Later record', 'Earlier record'])
            ->set('sortOrder', 'name_asc')
            ->assertSeeInOrder(['Earlier record', 'Later record'])
            ->set('search', 'Earlier')
            ->assertSee('Earlier record')
            ->assertDontSee('Later record');
    }

    public function test_records_list_shows_ram_storage_specs_without_selected_recommendations(): void
    {
        $office = Office::create(['name' => 'MIS Office', 'status' => 'active', 'computer_count' => 1]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'System Unit',
            'task_name' => 'Check RAM capacity and condition',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $record = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Test User',
            'position' => 'Technician',
            'date_started' => '2026-10-01',
            'status' => 'pending',
        ]);
        PmRecordItem::create([
            'pm_record_id' => $record->id,
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'defective',
            'remarks' => '8GB DDR4',
            'recommendation' => 'Increase RAM Capacity',
        ]);
        $storageItem = PmChecklistItem::create([
            'section' => 'System Unit',
            'task_name' => 'Check storage type and condition (SSD/HDD)',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        PmRecordItem::create([
            'pm_record_id' => $record->id,
            'pm_checklist_item_id' => $storageItem->id,
            'status' => 'defective',
            'remarks' => '200GB HDD',
            'recommendation' => 'Upgrade from HDD to SSD',
        ]);

        Livewire::test(PmRecordsList::class)
            ->assertSee('RAM / Storage Specs')
            ->assertSee('RAM: 8GB DDR4')
            ->assertSee('Storage: 200GB HDD')
            ->assertDontSee('Increase RAM Capacity');
    }

    public function test_records_list_shows_conducted_by_user_from_authenticated_account(): void
    {
        $office = Office::create(['name' => 'MIS Office', 'status' => 'active', 'computer_count' => 1]);
        $user = User::create([
            'name' => 'Maria Dela Cruz',
            'email' => 'maria@example.com',
            'password' => bcrypt('secret'),
            'position' => 'MIS Staff',
        ]);

        PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Test User',
            'position' => 'Technician',
            'date_started' => '2026-10-01',
            'conducted_by' => $user->id,
            'status' => 'pending',
        ]);

        Livewire::test(PmRecordsList::class)
            ->assertSee('Conducted By')
            ->assertSee('Maria Dela Cruz');
    }
}