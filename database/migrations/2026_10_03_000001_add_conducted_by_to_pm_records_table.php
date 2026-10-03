<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pm_records', function (Blueprint $table) {
            if (!Schema::hasColumn('pm_records', 'conducted_by')) {
                $table->foreignId('conducted_by')->nullable()->constrained('users')->after('position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pm_records', function (Blueprint $table) {
            if (Schema::hasColumn('pm_records', 'conducted_by')) {
                $table->dropConstrainedForeignId('conducted_by');
            }
        });
    }
};
