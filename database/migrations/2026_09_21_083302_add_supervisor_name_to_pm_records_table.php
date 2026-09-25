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
            $table->string('supervisor_name')->nullable()->after('representative_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pm_records', function (Blueprint $table) {
            $table->dropColumn('supervisor_name');
        });
    }
};