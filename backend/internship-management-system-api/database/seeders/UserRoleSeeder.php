<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Test',
                'last_name' => 'Student',
                'email' => 'student@example.com',
                'role' => 'Student',
                'password' => Hash::make('password123'),
                'must_change_password' => true
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Supervisor',
                'email' => 'supervisor@example.com',
                'role' => 'Supervisor',
                'password' => Hash::make('password123'),
                'must_change_password' => true
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Company',
                'email' => 'company@example.com',
                'role' => 'Company',
                'password' => Hash::make('password123'),
                'must_change_password' => true
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'External',
                'email' => 'external@example.com',
                'role' => 'External',
                'password' => Hash::make('password123'),
                'must_change_password' => true
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
