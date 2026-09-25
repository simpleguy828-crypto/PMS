<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\PmRecord;
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

    public function render()
    {
        return view('livewire.dashboard');
    }


}