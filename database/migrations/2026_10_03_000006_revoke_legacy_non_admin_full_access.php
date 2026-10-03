<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fullAccess = [
            'dashboard',
            'office-selection',
            'preventive-maintenance-form',
            'pm-records-list',
            'office-manager',
            'pm-schedule-manager',
            'pm-monthly-summary-report',
            'account-manager',
            'position-manager',
        ];

        foreach (DB::table('positions')->get() as $position) {
            $permissions = json_decode($position->module_access ?? '[]', true) ?: [];
            $hasFullAccess = count($permissions) === count($fullAccess)
                && empty(array_diff($fullAccess, $permissions));

            if (strcasecmp($position->name, 'Admin') !== 0 && $hasFullAccess) {
                DB::table('positions')->where('id', $position->id)->update([
                    'module_access' => json_encode([]),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Permissions are assigned explicitly in Position Manager after the conversion.
    }
};
