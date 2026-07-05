<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'HR Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $employeeUser = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Demo Employee',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]
        );

        Employee::firstOrCreate(
            ['user_id' => $employeeUser->id],
            [
                'employee_code' => 'EMP-0001',
                'position' => 'Staff',
                'department' => 'Operations',
                'status' => 'active',
            ]
        );
    }
}
