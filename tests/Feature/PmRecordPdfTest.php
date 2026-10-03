<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\PmRecord;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PmRecordPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_pdf_uses_the_logged_in_name_below_the_signature(): void
    {
        $user = User::factory()->create([
            'name' => 'MIS Account Name',
            'position' => 'MIS Officer',
        ]);
        $office = Office::create([
            'name' => 'MIS Office',
            'status' => 'active',
            'computer_count' => 1,
        ]);
        $record = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Person Who Requested PM',
            'position' => 'Faculty',
            'date_started' => '2026-10-02',
            'status' => 'pending',
        ]);

        $pdfDocument = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfDocument->shouldReceive('setPaper')->once()->with([0, 0, 612, 936], 'portrait')->andReturnSelf();
        $pdfDocument->shouldReceive('download')->once()->with('pm-record-' . $record->id . '.pdf')->andReturn(response('PDF', 200));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.pm-record', \Mockery::on(function ($data) use ($record) {
                $this->assertSame($record->id, $data['record']->id);
                $this->assertSame('MIS Account Name', $data['preparedBy']);
                $this->assertSame('MIS Officer', $data['preparedByPosition']);

                return true;
            }))
            ->andReturn($pdfDocument);

        $this->actingAs($user)
            ->get(route('pm-record.pdf', ['id' => $record->id]))
            ->assertOk();
    }

    public function test_record_pdf_uses_signature_line_and_mis_staff_fallback_when_logged_out(): void
    {
        $office = Office::create([
            'name' => 'MIS Office',
            'status' => 'active',
            'computer_count' => 1,
        ]);
        $record = PmRecord::create([
            'office_id' => $office->id,
            'requested_by_name' => 'Person Who Requested PM',
            'position' => 'Faculty',
            'date_started' => '2026-10-02',
            'status' => 'pending',
        ]);

        $pdfDocument = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfDocument->shouldReceive('setPaper')->once()->with([0, 0, 612, 936], 'portrait')->andReturnSelf();
        $pdfDocument->shouldReceive('download')->once()->with('pm-record-' . $record->id . '.pdf')->andReturn(response('PDF', 200));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.pm-record', \Mockery::on(function ($data) use ($record) {
                $this->assertSame($record->id, $data['record']->id);
                $this->assertSame('', $data['preparedBy']);
                $this->assertSame('MIS Staff', $data['preparedByPosition']);

                return true;
            }))
            ->andReturn($pdfDocument);

        $this->get(route('pm-record.pdf', ['id' => $record->id]))
            ->assertOk();
    }
}