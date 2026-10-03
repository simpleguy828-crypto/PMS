<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\PmChecklistItem;
use App\Models\PmRecord;
use App\Models\PmRecordItem;
use Illuminate\Database\Seeder;
use RuntimeException;

class PmDemoRecordsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('PM demo records cannot be seeded in production.');
        }

        $today = now()->toDateString();
        $office = Office::updateOrCreate(
            ['name' => 'PM Demo Office (TEST ONLY)'],
            ['department' => 'TEST DATA', 'status' => 'active', 'computer_count' => 2]
        );

        $requiredTasks = [
            'Check if mouse is responsive',
            'Check for cable cuts or damages',
            'Clean dust and other foreign object inside the keyboard',
            'Check if the keys are in-place',
            'Check RAM capacity and condition',
            'Check storage type and condition (SSD/HDD)',
            'Check for fan irregular noise',
        ];
        $checklistItems = PmChecklistItem::where('is_active', true)->get()->keyBy('task_name');
        $missingTasks = array_diff($requiredTasks, $checklistItems->keys()->all());

        if ($missingTasks !== []) {
            throw new RuntimeException('Missing active checklist items: ' . implode(', ', $missingTasks));
        }

        $computerScenarios = [
            'DEMO COMPUTER 1 - TEST ONLY' => [
                'Check for cable cuts or damages' => ['status' => 'defective', 'recommendation' => 'Change Keyboard'],
                'Clean dust and other foreign object inside the keyboard' => ['status' => 'done'],
                'Check if the keys are in-place' => ['status' => 'defective', 'recommendation' => 'Change Keyboard'],
                'Check if mouse is responsive' => ['status' => 'defective', 'recommendation' => 'Change Mouse'],
                'Check RAM capacity and condition' => ['status' => 'defective', 'remarks' => '8GB DDR4', 'recommendation' => 'Increase RAM Capacity'],
                'Check storage type and condition (SSD/HDD)' => ['status' => 'defective', 'remarks' => '200GB HDD', 'recommendation' => 'Upgrade from HDD to SSD'],
                'Check for fan irregular noise' => ['status' => 'defective'],
            ],
            'DEMO COMPUTER 2 - TEST ONLY' => [
                'Check for cable cuts or damages' => ['status' => 'good'],
                'Check if mouse is responsive' => ['status' => 'good'],
                'Check RAM capacity and condition' => ['status' => 'good', 'remarks' => '16GB DDR4'],
                'Check storage type and condition (SSD/HDD)' => ['status' => 'defective', 'remarks' => '500GB HDD', 'recommendation' => 'Upgrade Storage Capacity'],
                'Check for fan irregular noise' => ['status' => 'defective'],
            ],
        ];

        foreach ($computerScenarios as $name => $overrides) {
            $record = PmRecord::updateOrCreate(
                [
                    'office_id' => $office->id,
                    'requested_by_name' => $name,
                    'date_started' => $today,
                ],
                ['position' => 'Test Data (Demo)', 'status' => 'pending']
            );

            foreach ($checklistItems as $checklistItem) {
                $values = $overrides[$checklistItem->task_name] ?? [];
                PmRecordItem::updateOrCreate(
                    [
                        'pm_record_id' => $record->id,
                        'pm_checklist_item_id' => $checklistItem->id,
                    ],
                    [
                        'status' => $values['status'] ?? 'good',
                        'date_completed' => $today,
                        'remarks' => $values['remarks'] ?? null,
                        'recommendation' => $values['recommendation'] ?? null,
                    ]
                );
            }
        }

        $summaryUrl = '/pm-summary/pdf?start_date=' . $today . '&end_date=' . $today . '&office_id=' . $office->id;
        $this->command->info('PM demo records ready in ' . $office->name . ' (office ID ' . $office->id . ').');
        $this->command->info('Open /pm-records-list to view each record and its PDF.');
        $this->command->info('Generate the demo summary at ' . $summaryUrl);
    }
}