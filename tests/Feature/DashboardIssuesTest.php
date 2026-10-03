<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Office;
use App\Models\PmChecklistItem;
use App\Models\PmRecord;
use App\Models\PmRecordItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardIssuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_total_and_breakdown_count_defective_items_separately_from_damaged_computers(): void
    {
        $officeOne = $this->makeOffice('Office One');
        $officeTwo = $this->makeOffice('Office Two');
        $officeThree = $this->makeOffice('Office Three');
        $mouseItem = $this->makeChecklistItem('Mouse', 'Mouse cable has cuts or damages');
        $keyboardItem = $this->makeChecklistItem('Keyboard', 'Keyboard cable has cuts or damages');
        $duplicateKeyboardItem = $this->makeChecklistItem('Keyboard', 'Keyboard cable has cuts or damages', 'Inspect keyboard cable');
        $keyboardKeysItem = $this->makeChecklistItem('Keyboard', 'Some keys are missing or not in place');

        foreach (range(1, 3) as $computer) {
            $this->makeDefectiveRecord($officeOne, [$mouseItem]);
        }
        $this->makeDefectiveRecord($officeTwo, [$mouseItem, $keyboardItem, $duplicateKeyboardItem, $keyboardKeysItem]);
        $this->makeDefectiveRecord($officeThree, [$mouseItem]);
        $this->makeDefectiveRecord($officeThree, [$mouseItem], '2026-09-30');

        $dashboard = Livewire::test(Dashboard::class)
            ->set('customStartDate', '2026-10-01')
            ->set('customEndDate', '2026-10-01')
            ->assertSet('totalIssuesFound', 7)
            ->assertSet('totalDamagedComputers', 5)
            ->call('toggleIssueBreakdown')
            ->assertSee('Mouse cable has cuts or damages - 5 computers')
            ->assertSee('Office One')
            ->assertSee('Office Three')
            ->assertSee('Keyboard cable has cuts or damages - 1 computer')
            ->assertSee('Some keys are missing or not in place - 1 computer')
            ->assertSee('Office Two');

        $dashboard->set('selectedOfficeId', $officeTwo->id)
            ->assertSet('totalIssuesFound', 3)
            ->assertSet('totalDamagedComputers', 1)
            ->assertSee('Mouse cable has cuts or damages - 1 computer')
            ->assertSee('Keyboard cable has cuts or damages - 1 computer')
            ->assertSee('Some keys are missing or not in place - 1 computer')
            ->assertSet('issueBreakdown.Mouse.findings.0.offices.0.name', 'Office Two')
            ->assertSet('issueBreakdown.Keyboard.findings.0.offices.0.name', 'Office Two')
            ->assertSee('max-h-80 overflow-y-auto', false);

        $dashboard->call('openIssueBreakdownModal')
            ->assertSet('showIssueBreakdownModal', true)
            ->assertSee('role="dialog"', false)
            ->assertSee('overflow-y-auto overscroll-contain p-5', false)
            ->call('closeIssueBreakdownModal')
            ->assertSet('showIssueBreakdownModal', false);
    }

    private function makeOffice(string $name): Office
    {
        return Office::create([
            'name' => $name,
            'status' => 'active',
            'computer_count' => 5,
        ]);
    }

    private function makeChecklistItem(string $section, string $findingLabel, ?string $taskName = null): PmChecklistItem
    {
        return PmChecklistItem::create([
            'section' => $section,
            'task_name' => $taskName ?? 'Check ' . strtolower($section),
            'finding_label' => $findingLabel,
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function makeDefectiveRecord(Office $office, array $checklistItems, string $date = '2026-10-01'): void
    {
        $record = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Test User',
            'position' => 'Technician',
            'date_started' => $date,
            'status' => 'pending',
        ]);

        foreach ($checklistItems as $checklistItem) {
            PmRecordItem::create([
                'pm_record_id' => $record->id,
                'pm_checklist_item_id' => $checklistItem->id,
                'status' => 'defective',
            ]);
        }
    }
}