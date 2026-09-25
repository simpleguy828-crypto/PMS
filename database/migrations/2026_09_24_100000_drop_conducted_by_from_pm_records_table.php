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
            $table->dropForeign(['conducted_by']);
            $table->dropColumn('conducted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pm_records', function (Blueprint $table) {
            $table->foreignId('conducted_by')->constrained('users');
        });
    }
};