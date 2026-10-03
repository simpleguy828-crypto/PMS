<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Position::updateOrCreate(
            ['name' => 'Admin'],
            ['module_access' => array_keys(User::moduleOptions())]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@pm.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin123!'),
                'position' => 'Admin',
                'department' => 'MIS',
            ]
        );

        $this->command->info('Admin user ready: ' . $admin->email . ' / Admin123!');
    }
}
