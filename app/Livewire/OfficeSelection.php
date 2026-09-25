<?php

namespace App\Livewire;

use App\Models\Office;
use Livewire\Component;
use App\Livewire\Concerns\NavigatesPages;

class OfficeSelection extends Component
{
    use NavigatesPages;
    public function selectOffice($officeId)
    {
        // Redirect to the preventive maintenance form with the selected office ID
        return redirect()->route('preventive-maintenance-form', ['officeId' => $officeId]);
    }

    public function render()
    {
        $offices = Office::where('status', 'active')->withCount('pmRecords')->get();
        return view('livewire.office-selection', compact('offices'));
    }
}