<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('scheduled_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pm_schedule_offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->date('current_scheduled_date');
            $table->timestamps();
            $table->unique(['pm_schedule_id', 'office_id']);
        });

        Schema::create('pm_schedule_reschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_schedule_office_id')->constrained()->cascadeOnDelete();
            $table->date('old_date');
            $table->date('new_date');
            $table->timestamp('rescheduled_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_schedule_reschedules');
        Schema::dropIfExists('pm_schedule_offices');
        Schema::dropIfExists('pm_schedules');
    }
};