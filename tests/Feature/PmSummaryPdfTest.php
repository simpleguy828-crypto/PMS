<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\PmChecklistItem;
use App\Models\PmRecord;
use App\Models\PmRecordItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PmSummaryPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_generate_a_downloadable_summary_pdf(): void
    {
        $office = Office::create([
            'name' => 'Guidance Office',
            'status' => 'active',
            'computer_count' => 3,
        ]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'Keyboard',
            'task_name' => 'Check keyboard cable',
            'finding_label' => 'Keyboard cable has cuts or damages',
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
            'remarks' => 'Replace the keyboard',
        ]);

        $response = $this->get(route('pm-summary.pdf', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'office_id' => $office->id,
        ]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('computer-pm-summary-2026-10-01-to-2026-10-31.pdf', $response->headers->get('content-disposition'));
    }

    public function test_unfiltered_summary_uses_various_offices_and_counts_recommendations_by_computer(): void
    {
        $officeOne = Office::create(['name' => 'Office One', 'status' => 'active', 'computer_count' => 4]);
        $officeTwo = Office::create(['name' => 'Office Two', 'status' => 'active', 'computer_count' => 2]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'Mouse',
            'task_name' => 'Check mouse cable',
            'finding_label' => 'Mouse cable has cuts or damages',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        foreach ([
            [$officeOne, 'Change the mouse'],
            [$officeOne, 'change the mouse'],
            [$officeOne, 'Repair the mouse'],
        ] as [$office, $remark]) {
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
                'remarks' => $remark,
            ]);
        }
        PmRecord::create([
            'office_id' => $officeTwo->id,
            'requested_by_name' => 'Test User',
            'position' => 'Technician',
            'date_started' => '2026-10-01',
            'status' => 'pending',
        ]);

        $pdfDocument = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfDocument->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $pdfDocument->shouldReceive('download')->once()->with('computer-pm-summary-2026-10-01-to-2026-10-31.pdf')->andReturn(response('PDF', 200));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.pm-summary', \Mockery::on(function ($data) {
                $office = $data['officeSummaries']->firstWhere('name', 'Office One');
                $this->assertSame('VARIOUS OFFICES', $data['officeLabel']);
                $this->assertSame([
                    ['text' => 'Change the mouse', 'computers' => 2],
                    ['text' => 'Repair the mouse', 'computers' => 1],
                ], $office['recommendations']->all());

                return true;
            }))
            ->andReturn($pdfDocument);

        $this->get(route('pm-summary.pdf', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]))->assertOk();
    }

    public function test_summary_uses_recommendations_without_including_specs(): void
    {
        $office = Office::create(['name' => 'Office One', 'status' => 'active', 'computer_count' => 4]);
        $checklistItem = PmChecklistItem::create([
            'section' => 'Storage',
            'task_name' => 'Check storage drive',
            'finding_label' => 'Storage drive performance degradation',
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
            'remarks' => '200GB HDD',
            'recommendation' => 'Upgrade from HDD to SSD',
        ]);

        $recordTwo = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Another User',
            'position' => 'IT Staff',
            'date_started' => '2026-10-02',
            'status' => 'pending',
        ]);

        PmRecordItem::create([
            'pm_record_id' => $recordTwo->id,
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'defective',
            'remarks' => '8GB RAM DDR4',
            'recommendation' => 'NONE',
        ]);

        $recordThree = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Third User',
            'position' => 'IT Staff',
            'date_started' => '2026-10-03',
            'status' => 'pending',
        ]);

        PmRecordItem::create([
            'pm_record_id' => $recordThree->id,
            'pm_checklist_item_id' => $checklistItem->id,
            'status' => 'defective',
            'remarks' => '16GB DDR4 RAM',
            'recommendation' => null,
        ]);

        $keyboardItem = PmChecklistItem::create([
            'section' => 'Keyboard',
            'task_name' => 'Clean dust and other foreign object inside the keyboard',
            'finding_label' => 'Keyboard has dust or foreign objects inside',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        PmRecordItem::create([
            'pm_record_id' => $recordThree->id,
            'pm_checklist_item_id' => $keyboardItem->id,
            'status' => 'done',
            'remarks' => null,
            'recommendation' => null,
        ]);

        $pdfDocument = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfDocument->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $pdfDocument->shouldReceive('download')->once()->with('computer-pm-summary-2026-10-01-to-2026-10-31.pdf')->andReturn(response('PDF', 200));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.pm-summary', \Mockery::on(function ($data) {
                $office = $data['officeSummaries']->firstWhere('name', 'Office One');
                $this->assertSame([
                    ['text' => 'Upgrade from HDD to SSD', 'computers' => 1],
                ], $office['recommendations']->all());

                return true;
            }))
            ->andReturn($pdfDocument);

        $this->get(route('pm-summary.pdf', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]))->assertOk();
    }

    public function test_three_keyboard_findings_with_the_same_remark_count_as_one_computer(): void
    {
        $office = Office::create(['name' => 'Office One', 'status' => 'active', 'computer_count' => 1]);
        $record = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Test User',
            'position' => 'Technician',
            'date_started' => '2026-10-01',
            'status' => 'pending',
        ]);

        foreach ([
            ['Check for cable cuts or damages', 'Keyboard cable has cuts or damages'],
            ['Clean dust and other foreign object inside the keyboard', 'Keyboard has dust or foreign objects inside'],
            ['Check if the keys are in-place', 'Some keys are missing or not in place'],
        ] as $index => [$taskName, $findingLabel]) {
            $checklistItem = PmChecklistItem::create([
                'section' => 'Keyboard',
                'task_name' => $taskName,
                'finding_label' => $findingLabel,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            PmRecordItem::create([
                'pm_record_id' => $record->id,
                'pm_checklist_item_id' => $checklistItem->id,
                'status' => 'defective',
                'recommendation' => 'Change Keyboard',
            ]);
        }

        $pdfDocument = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfDocument->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $pdfDocument->shouldReceive('download')->once()->with('computer-pm-summary-2026-10-01-to-2026-10-31.pdf')->andReturn(response('PDF', 200));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.pm-summary', \Mockery::on(function ($data) {
                $office = $data['officeSummaries']->firstWhere('name', 'Office One');
                $this->assertSame([
                    ['text' => 'Change Keyboard', 'computers' => 1],
                ], $office['recommendations']->all());

                return true;
            }))
            ->andReturn($pdfDocument);

        $this->get(route('pm-summary.pdf', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]))->assertOk();
    }
}