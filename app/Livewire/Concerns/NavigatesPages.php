<?php

namespace App\Livewire\Concerns;

trait NavigatesPages
{
    public function navigateToSection($section)
    {
        switch ($section) {
            case 'dashboard':
                return redirect()->route('dashboard');
            case 'office-selection':
                return redirect()->route('office-selection');
            case 'pm-records-list':
                return redirect()->route('pm-records-list');
            case 'office-manager':
                return redirect()->route('office-manager');
            case 'pm-schedule-manager':
                return redirect()->route('pm-schedule-manager');
            case 'admin.accounts':
                return redirect()->route('admin.accounts');
            case 'profile':
                return redirect()->route('profile');
            default:
                break;
        }
    }
}
