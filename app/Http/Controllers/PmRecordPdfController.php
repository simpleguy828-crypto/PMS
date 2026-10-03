<?php

namespace App\Http\Controllers;

use App\Models\PmRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class PmRecordPdfController extends Controller
{
    public function __invoke($id)
    {
        $record = PmRecord::with(['office', 'recordItems.checklistItem'])->findOrFail($id);

        $record->requested_by_name = $this->sanitizeString($record->requested_by_name);
        $record->position = $this->sanitizeString($record->position);

        if ($record->office) {
            $record->office->name = $this->sanitizeString($record->office->name);
            $record->office->department = $this->sanitizeString($record->office->department);
        }

        foreach ($record->recordItems as $item) {
            if ($item->checklistItem) {
                $item->checklistItem->task_name = $this->sanitizeString($item->checklistItem->task_name);
            }

            $item->remarks = $this->sanitizeString($item->remarks);
            $item->recommendation = $this->sanitizeString($item->recommendation);
        }

        $preparedBy = $this->sanitizeString($record->conductedBy?->name);
        $preparedByPosition = $this->sanitizeString($record->conductedBy?->position ?: 'MIS Staff');

        return Pdf::loadView('pdf.pm-record', [
            'record' => $record,
            'preparedBy' => $this->sanitizeString($preparedBy),
            'preparedByPosition' => $this->sanitizeString($preparedByPosition),
        ])
            ->setPaper([0, 0, 612, 936], 'portrait')
            ->download('pm-record-' . $record->id . '.pdf');
    }

    private function sanitizeString($value): string
    {
        if ($value === null) {
            return '';
        }

        return iconv('UTF-8', 'UTF-8//IGNORE', (string) $value) ?: '';
    }
}