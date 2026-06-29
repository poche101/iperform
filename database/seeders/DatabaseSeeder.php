<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Renamed variable from $hr to match its assignment concept
        $staffPerformance = User::create([
            'name' => 'Sis. Olachi Chukwutame',
            'username' => 'staff_performance',
            'email' => 'hr@iperform.app',
            'password' => Hash::make('Performance2026'),
            'role' => 'staff_performance',
        ]);
    }
}
