<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyPosition = DB::table('positions')->where('name', 'IT Manager')->first();

        if ($legacyPosition && ! DB::table('users')->where('position', $legacyPosition->name)->exists()) {
            DB::table('positions')->where('id', $legacyPosition->id)->update([
                'module_access' => json_encode([]),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Permission assignments cannot be restored without recreating legacy user roles.
    }
};
