<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\PmRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PmSummaryPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'office_id' => 'nullable|integer|exists:offices,id',
        ]);

        $startDate = Carbon::parse($filters['start_date'])->startOfDay();
        $endDate = Carbon::parse($filters['end_date'])->endOfDay();
        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $records = PmRecord::with(['recordItems.checklistItem'])
            ->whereBetween('date_started', [$startDate, $endDate])
            ->when($filters['office_id'] ?? null, fn ($query, $officeId) => $query->where('office_id', $officeId))
            ->get();
        $recordsByOffice = $records->groupBy('office_id');

        $offices = !empty($filters['office_id'])
            ? Office::whereKey($filters['office_id'])->get()
            : Office::whereIn('id', $recordsByOffice->keys())->orderBy('name')->get();

        $officeSummaries = $offices->map(function ($office) use ($recordsByOffice) {
            $officeRecords = $recordsByOffice->get($office->id, collect());
            $computerFindings = [];
            $recommendations = [];

            foreach ($officeRecords as $record) {
                foreach ($record->recordItems->where('status', 'defective') as $recordItem) {
                    $checklistItem = $recordItem->checklistItem;
                    $finding = $checklistItem?->finding_label ?: $checklistItem?->task_name ?: 'Unspecified finding';
                    $computerFindings[$finding][$record->id] = true;

                    $recommendation = $recordItem->recommendation !== null
                        ? $this->normalizeRecommendationText($recordItem->recommendation)
                        : $this->normalizeLegacyRecommendationText((string) $recordItem->remarks);
                    if ($recommendation !== null && $recommendation !== '') {
                        $recommendationKey = mb_strtolower(preg_replace('/\s+/u', ' ', $recommendation));
                        $recommendations[$recommendationKey] ??= [
                            'text' => $recommendation,
                            'computers' => [],
                        ];
                        $recommendations[$recommendationKey]['computers'][$record->id] = true;
                    }
                }
            }

            $findings = collect($computerFindings)
                ->map(fn ($computers, $finding) => [
                    'name' => $finding,
                    'computers' => count($computers),
                ])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            return [
                'name' => $office->name,
                'computers_checked' => $officeRecords->count(),
                'issues_found' => (int) $findings->sum('computers'),
                'findings' => $findings,
                'recommendations' => collect($recommendations)
                    ->map(fn ($recommendation) => [
                        'text' => $recommendation['text'],
                        'computers' => count($recommendation['computers']),
                    ])
                    ->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values(),
            ];
        });

        $totalComputersChecked = $officeSummaries->sum('computers_checked');
        $totalIssuesFound = $officeSummaries->sum('issues_found');
        $periodLabel = $startDate->format('Y-m') === $endDate->format('Y-m')
            ? strtoupper($startDate->format('F Y'))
            : $startDate->format('F j, Y') . ' - ' . $endDate->format('F j, Y');
        $officeLabel = !empty($filters['office_id'])
            ? ($offices->first()->name ?? 'Selected office')
            : 'VARIOUS OFFICES';
        $tasksPerformed = [
            'Check RAM & Storage',
            'Check hardware cables',
            'Antivirus scan',
            'Check for corrupt files',
            'Clean the keyboard',
        ];

        $filename = 'computer-pm-summary-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';

        return Pdf::loadView('pdf.pm-summary', compact(
            'startDate',
            'endDate',
            'periodLabel',
            'officeLabel',
            'officeSummaries',
            'totalComputersChecked',
            'totalIssuesFound',
            'tasksPerformed'
        ))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    protected function normalizeRecommendationText(?string $remark): ?string
    {
        $text = trim((string) $remark);
        if ($text === '') {
            return null;
        }

        $parts = preg_split('/\s+[–—-]\s+/u', $text);
        $recommendation = $parts !== false && count($parts) > 1
            ? trim((string) end($parts))
            : $text;

        $recommendation = preg_replace('/\s+/u', ' ', $recommendation);
        $recommendation = trim((string) $recommendation);

        if ($recommendation === '') {
            return null;
        }

        $normalized = mb_strtolower($recommendation);
        if (in_array($normalized, ['none', 'n/a', 'na', 'no issue', 'not applicable'], true)) {
            return null;
        }

        return $recommendation;
    }

    protected function normalizeLegacyRecommendationText(?string $remark): ?string
    {
        $text = trim((string) $remark);
        if (preg_match('/\s+[–—-]\s+/u', $text) || preg_match('/^(change|repair|replace|upgrade|increase|clean|service|install|update|reinstall)\b/iu', $text)) {
            return $this->normalizeRecommendationText($text);
        }

        return null;
    }
}