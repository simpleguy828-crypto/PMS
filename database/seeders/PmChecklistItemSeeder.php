<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PmChecklistItem;
use App\Models\PmRecordItem;

class PmChecklistItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear dependent records first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PmRecordItem::truncate();
        PmChecklistItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $items = [
            // Monitor
            ['section' => 'Monitor', 'task_name' => 'Check for vertical lines', 'finding_label' => 'Screen has vertical lines', 'sort_order' => 1, 'is_active' => true],
            ['section' => 'Monitor', 'task_name' => 'Check power cable for cuts or damages', 'finding_label' => 'Power cable has cuts or damages', 'sort_order' => 2, 'is_active' => true],
            ['section' => 'Monitor', 'task_name' => 'Check VGA/HDMI for cuts or damages', 'finding_label' => 'VGA/HDMI cable has cuts or damages', 'sort_order' => 3, 'is_active' => true],
            ['section' => 'Monitor', 'task_name' => 'Check for vertical lines', 'finding_label' => 'Screen has vertical lines', 'sort_order' => 4, 'is_active' => true], // duplicate as per user

            // Mouse
            ['section' => 'Mouse', 'task_name' => 'Check cable for cuts or damages', 'finding_label' => 'Mouse cable has cuts or damages', 'sort_order' => 5, 'is_active' => true],
            ['section' => 'Mouse', 'task_name' => 'Check if mouse is responsive', 'finding_label' => 'Mouse is not responsive', 'sort_order' => 6, 'is_active' => true],

            // Keyboard
            ['section' => 'Keyboard', 'task_name' => 'Check for cable cuts or damages', 'finding_label' => 'Keyboard cable has cuts or damages', 'sort_order' => 7, 'is_active' => true],
            ['section' => 'Keyboard', 'task_name' => 'Clean dust and other foreign object inside the keyboard', 'finding_label' => 'Keyboard has dust or foreign objects inside', 'sort_order' => 8, 'is_active' => true],
            ['section' => 'Keyboard', 'task_name' => 'Check if the keys are in-place', 'finding_label' => 'Some keys are missing or not in place', 'sort_order' => 9, 'is_active' => true],

            // System Unit
            ['section' => 'System Unit', 'task_name' => 'Check for power cable for cuts or damages', 'finding_label' => 'Power cable has cuts or damages', 'sort_order' => 10, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check for power supply cables integrity', 'finding_label' => 'Power supply cables have integrity issues', 'sort_order' => 11, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check for fan irregular noise', 'finding_label' => 'Fan has irregular noise', 'sort_order' => 12, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check if USB ports are working', 'finding_label' => 'USB ports are not working', 'sort_order' => 13, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check if ethernet port is working', 'finding_label' => 'Ethernet port is not working', 'sort_order' => 14, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check if CMOS is working', 'finding_label' => 'CMOS is not working', 'sort_order' => 15, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check if windows is updated', 'finding_label' => 'Windows is not updated', 'sort_order' => 16, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check if updated anti-virus', 'finding_label' => 'Anti-virus is not updated', 'sort_order' => 17, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check if password protected', 'finding_label' => 'System is not password protected', 'sort_order' => 18, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check RAM capacity and condition', 'finding_label' => 'RAM issue detected', 'sort_order' => 19, 'is_active' => true],
            ['section' => 'System Unit', 'task_name' => 'Check storage type and condition (SSD/HDD)', 'finding_label' => 'Storage device issue detected', 'sort_order' => 20, 'is_active' => true],
        ];

        foreach ($items as $item) {
            PmChecklistItem::create($item);
        }
    }
}