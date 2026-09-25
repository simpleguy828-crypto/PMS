<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Office;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            ['name' => 'Office of the President', 'department' => 'Executive', 'status' => 'active', 'computer_count' => 15],
            ['name' => 'Office of the Vice President for Academic Affairs', 'department' => 'Academic Affairs', 'status' => 'active', 'computer_count' => 25],
            ['name' => 'Office of the Vice President for Administration and Finance', 'department' => 'Administration and Finance', 'status' => 'active', 'computer_count' => 20],
            ['name' => 'Office of the Vice President for Research and Extension', 'department' => 'Research and Extension', 'status' => 'active', 'computer_count' => 18],
            ['name' => 'Office of the Student Affairs', 'department' => 'Student Affairs', 'status' => 'active', 'computer_count' => 12],
            ['name' => 'Management Information Systems (MIS) Office', 'department' => 'MIS', 'status' => 'active', 'computer_count' => 30],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(['name' => $office['name']], $office);
        }
    }
}
