<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminPositionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PmRecordPdfController;
use App\Http\Controllers\PmSummaryPdfController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Dashboard;
use App\Livewire\OfficeManager;
use App\Livewire\OfficeSelection;
use App\Livewire\PmRecordsList;
use App\Livewire\PmScheduleManager;
use App\Livewire\PreventiveMaintenanceForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->firstAccessibleRouteName());
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('module.access:dashboard')->get('/dashboard', Dashboard::class)->name('dashboard');
    Route::middleware('module.access:office-selection')->get('/office-selection', OfficeSelection::class)->name('office-selection');
    Route::middleware('module.access:preventive-maintenance-form')->get('/preventive-maintenance-form/{officeId}', PreventiveMaintenanceForm::class)->name('preventive-maintenance-form');
    Route::middleware('module.access:preventive-maintenance-form')->get('/preventive-maintenance-form/edit/{record}', PreventiveMaintenanceForm::class)->name('pm-form.edit');
    Route::middleware('module.access:office-manager')->get('/office-manager', OfficeManager::class)->name('office-manager');
    Route::middleware('module.access:pm-records-list')->get('/pm-records-list', PmRecordsList::class)->name('pm-records-list');
    Route::middleware('module.access:pm-schedule-manager')->get('/pm-schedule-manager', PmScheduleManager::class)->name('pm-schedule-manager');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('admin.access')->get('/admin/accounts', [AdminAccountController::class, 'index'])->name('admin.accounts');
    Route::middleware('admin.access')->post('/admin/accounts', [AdminAccountController::class, 'store'])->name('admin.accounts.store');
    Route::middleware('admin.access')->put('/admin/accounts/{user}', [AdminAccountController::class, 'update'])->name('admin.accounts.update');
    Route::middleware('admin.access')->delete('/admin/accounts/{user}', [AdminAccountController::class, 'destroy'])->name('admin.accounts.destroy');
    Route::middleware('module.access:position-manager')->get('/admin/positions', [AdminPositionController::class, 'index'])->name('admin.positions');
    Route::middleware('module.access:position-manager')->post('/admin/positions', [AdminPositionController::class, 'store'])->name('admin.positions.store');
    Route::middleware('module.access:position-manager')->put('/admin/positions/{position}', [AdminPositionController::class, 'update'])->name('admin.positions.update');
    Route::middleware('module.access:position-manager')->delete('/admin/positions/{position}', [AdminPositionController::class, 'destroy'])->name('admin.positions.destroy');

    Route::get('/pm-record/{id}/pdf', PmRecordPdfController::class)->name('pm-record.pdf');
    Route::get('/pm-summary/pdf', PmSummaryPdfController::class)->name('pm-summary.pdf');
    Route::get('/test', fn () => view('test'));
});