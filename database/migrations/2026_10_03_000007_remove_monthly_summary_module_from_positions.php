<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('positions')->get() as $position) {
            $permissions = json_decode($position->module_access ?? '[]', true) ?: [];
            $permissions = array_values(array_filter($permissions, fn ($module) => $module !== 'pm-monthly-summary-report'));

            DB::table('positions')->where('id', $position->id)->update([
                'module_access' => json_encode($permissions),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Removed module permissions are not restored on rollback.
    }
};
