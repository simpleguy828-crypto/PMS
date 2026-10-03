<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\PmRecord;
use App\Models\PmRecordItem;
use Illuminate\Support\Carbon;
use Livewire\Component;
use App\Livewire\Concerns\NavigatesPages;


class Dashboard extends Component
{
    use NavigatesPages;
    public $selectedOfficeId = '';

    public $customStartDate = '';

    public $customEndDate = '';

    public $allActiveOffices;

    public $totalComputersChecked = 0;

    public $totalOfficesChecked = 0;

    public $totalOfficesNotChecked = 0;

    public $totalDamagedComputers = 0;

    public $totalIssuesFound = 0;

    public $issueBreakdown = [];

    public $showIssueBreakdown = false;

    public $showIssueBreakdownModal = false;

    public $officesChecked = [];

    public function mount()
    {
        $this->customStartDate = Carbon::now()->startOfMonth()->toDateString();
        $this->customEndDate = Carbon::now()->toDateString();

        // Load all active offices
        $this->allActiveOffices = Office::where('status', 'active')->get();
        // Compute initial values
        $this->computeAnalytics();
    }

    public function getDateRangeFilter()
    {
        $now = Carbon::now();

        $start = $this->customStartDate
            ? Carbon::parse($this->customStartDate)->startOfDay()
            : $now->copy()->startOfMonth();
        $end = $this->customEndDate
            ? Carbon::parse($this->customEndDate)->endOfDay()
            : $now->copy()->endOfDay();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    public function getDateFilteredRecordsQuery()
    {
        [$startDate, $endDate] = $this->getDateRangeFilter();

        return PmRecord::query()
            ->whereBetween('date_started', [$startDate, $endDate]);
    }

    public function getFilteredRecordsQuery()
    {
        $query = $this->getDateFilteredRecordsQuery();

        if ($this->selectedOfficeId && $this->selectedOfficeId !== '') {
            $query->where('office_id', $this->selectedOfficeId);
        }

        return $query;
    }

    public function computeAnalytics()
    {
        // Reset computed values
        $this->totalComputersChecked = 0;
        $this->totalOfficesChecked = 0;
        $this->totalOfficesNotChecked = 0;
        $this->totalDamagedComputers = 0;
        $this->totalIssuesFound = 0;
        $this->issueBreakdown = [];
        $this->officesChecked = [];

        // Computer metrics respect both the date range and selected office.
        $query = $this->getFilteredRecordsQuery();

        // Office coverage respects the date range but always includes every office.
        $coverageQuery = $this->getDateFilteredRecordsQuery();

        // Compute total offices checked (distinct offices with records)
        $this->totalOfficesChecked = $coverageQuery->distinct()->count('office_id');
        $this->officesChecked = $coverageQuery->distinct()->pluck('office_id')->all();
        $this->totalOfficesNotChecked = $this->allActiveOffices
            ->whereNotIn('id', $this->officesChecked)
            ->count();

        // Compute total damaged computers (records with at least one defective item)
        $this->totalDamagedComputers = $this->getFilteredRecordsQuery()->whereHas('recordItems', function ($q) {
            $q->where('status', 'defective');
        })->distinct()->count();

        [$startDate, $endDate] = $this->getDateRangeFilter();
        $issueRows = PmRecordItem::query()
            ->join('pm_records', 'pm_records.id', '=', 'pm_record_items.pm_record_id')
            ->join('pm_checklist_items', 'pm_checklist_items.id', '=', 'pm_record_items.pm_checklist_item_id')
            ->join('offices', 'offices.id', '=', 'pm_records.office_id')
            ->where('pm_record_items.status', 'defective')
            ->whereBetween('pm_records.date_started', [$startDate, $endDate])
            ->when($this->selectedOfficeId !== '', function ($query) {
                $query->where('pm_records.office_id', $this->selectedOfficeId);
            })
            ->select([
                'pm_checklist_items.section',
                'pm_records.office_id',
                'offices.name as office_name',
            ])
            ->selectRaw("COALESCE(NULLIF(pm_checklist_items.finding_label, ''), pm_checklist_items.task_name) as finding_name")
            ->selectRaw('COUNT(DISTINCT pm_records.id) as computer_count')
            ->groupByRaw("pm_checklist_items.section, COALESCE(NULLIF(pm_checklist_items.finding_label, ''), pm_checklist_items.task_name), pm_records.office_id, offices.name")
            ->orderBy('pm_checklist_items.section')
            ->orderBy('finding_name')
            ->orderBy('offices.name')
            ->get();

        $this->totalIssuesFound = (int) $issueRows->sum('computer_count');
        $this->issueBreakdown = $issueRows->groupBy('section')
            ->map(function ($sectionRows) {
                return [
                    'findings' => $sectionRows->groupBy('finding_name')
                        ->map(function ($findingRows, $findingName) {
                            return [
                                'name' => $findingName,
                                'total' => (int) $findingRows->sum('computer_count'),
                                'offices' => $findingRows->map(fn ($row) => [
                                    'name' => $row->office_name,
                                    'count' => (int) $row->computer_count,
                                ])->values()->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        // Compute total computers checked: each PM record represents exactly ONE
        // computer checked, regardless of the office's total computer_count.
        // e.g. Office A has 15 computers total, but only 5 PM records were
        // conducted for it this period → 5 computers checked, not 15 and not 75.
        $this->totalComputersChecked = $this->getFilteredRecordsQuery()->count();
    }

    public function updatedSelectedOfficeId()
    {
        $this->computeAnalytics();
    }

    public function updatedCustomStartDate()
    {
        $this->computeAnalytics();
    }

    public function updatedCustomEndDate()
    {
        $this->computeAnalytics();
    }

    public function toggleIssueBreakdown()
    {
        $this->showIssueBreakdown = !$this->showIssueBreakdown;
    }

    public function openIssueBreakdownModal()
    {
        $this->showIssueBreakdown = false;
        $this->showIssueBreakdownModal = true;
    }

    public function closeIssueBreakdownModal()
    {
        $this->showIssueBreakdownModal = false;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }


}