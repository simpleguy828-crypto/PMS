<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\PmRecord;
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

    public function resumeDraft($recordId)
    {
        $draft = PmRecord::where('status', 'draft')->findOrFail($recordId);

        return redirect()->route('pm-form.edit', ['record' => $draft->id]);
    }

    public function render()
    {
        $offices = Office::where('status', 'active')->withCount('pmRecords')->get();
        $draftsByOffice = PmRecord::whereIn('office_id', $offices->pluck('id'))
            ->where('status', 'draft')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('office_id')
            ->map(fn ($drafts) => $drafts->first());

        return view('livewire.office-selection', compact('offices', 'draftsByOffice'));
    }
}