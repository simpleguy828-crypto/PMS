<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $modules = [
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

    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->json('module_access')->nullable();
        });

        foreach (DB::table('positions')->get() as $position) {
            $assignedUsers = DB::table('users')->where('position', $position->name)->get();
            $isAdminPosition = strcasecmp($position->name, 'Admin') === 0;
            $permissions = [];

            if ($isAdminPosition) {
                $permissions = $this->modules;
            } else {
                foreach ($assignedUsers as $user) {
                    if ($user->role === 'admin') {
                        continue;
                    }
                    $permissions = array_merge($permissions, json_decode($user->module_access ?? '[]', true) ?: []);
                }
                $permissions = array_values(array_intersect($this->modules, array_unique($permissions)));
            }

            DB::table('positions')->where('id', $position->id)->update([
                'module_access' => json_encode($permissions),
                'updated_at' => now(),
            ]);
        }

        DB::table('positions')->updateOrInsert(
            ['name' => 'Admin'],
            ['name' => 'Admin', 'module_access' => json_encode($this->modules), 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('users')->where('role', 'admin')->update(['position' => 'Admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'module_access']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('conductor');
            $table->json('module_access')->nullable();
        });

        foreach (DB::table('users')->get() as $user) {
            $position = DB::table('positions')->where('name', $user->position)->first();
            $permissions = json_decode($position->module_access ?? '[]', true) ?: [];
            DB::table('users')->where('id', $user->id)->update([
                'role' => in_array('account-manager', $permissions, true) ? 'admin' : 'conductor',
                'module_access' => json_encode($permissions),
            ]);
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('module_access');
        });
    }
};
