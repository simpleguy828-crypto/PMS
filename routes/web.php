<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\OfficeSelection;
use App\Livewire\PreventiveMaintenanceForm;
use App\Livewire\PmRecordsList;
use App\Livewire\PmScheduleManager;
use App\Livewire\PmMonthlySummaryReport;
use App\Livewire\OfficeManager;
use App\Livewire\Dashboard;
use App\Http\Controllers\PmRecordPdfController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Dashboard/Landing Page
Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/dashboard', Dashboard::class)->name('dashboard');


// Office Selection (Entry Point for PM)
Route::get('/office-selection', OfficeSelection::class)->name('office-selection');

// Preventive Maintenance Form (with pre-selected office)
Route::get('/preventive-maintenance-form/{officeId}', PreventiveMaintenanceForm::class)
    ->name('preventive-maintenance-form');

// Preventive Maintenance Form — EDIT an existing record
// Parameter MUST be named {record}: PreventiveMaintenanceForm::mount($officeId, $record)
// binds it automatically because the name matches.
Route::get('/preventive-maintenance-form/edit/{record}', PreventiveMaintenanceForm::class)
    ->name('pm-form.edit');

// Office Manager
Route::get('/office-manager', OfficeManager::class)->name('office-manager');

// Other existing routes
Route::get('/pm-records-list', PmRecordsList::class)->name('pm-records-list');
Route::get('/pm-schedule-manager', PmScheduleManager::class)->name('pm-schedule-manager');
Route::get('/pm-monthly-summary-report', PmMonthlySummaryReport::class)->name('pm-monthly-summary-report');

// PDF download route
Route::get('/pm-record/{id}/pdf', PmRecordPdfController::class)
    ->name('pm-record.pdf');
Route::get('/test', function () { return view('test'); });