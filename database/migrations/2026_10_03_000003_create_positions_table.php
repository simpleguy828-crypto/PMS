<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $names = DB::table('users')
            ->whereNotNull('position')
            ->where('position', '<>', '')
            ->distinct()
            ->pluck('position')
            ->prepend('IT Manager')
            ->unique();

        foreach ($names as $name) {
            DB::table('positions')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
