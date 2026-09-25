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
        Schema::create('pm_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('pm_checklist_item_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->date('date_completed')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_record_items');
    }
};
