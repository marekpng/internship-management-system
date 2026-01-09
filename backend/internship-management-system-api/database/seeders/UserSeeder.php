<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Skontrolujeme, či existujú roly v databáze, ak nie, pridáme ich.
        $roles = [
            'student',
            'company',
            'garant',
            'admin',
            'external'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Definícia používateľov
        $users = [
            [
                'email' => 'student@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'student_email' => 'john.student@example.com',
                'phone' => '123456789',
                'role_names' => ['student'],
            ],
            [
                'email' => 'student2@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'John2',
                'last_name' => 'Doe2',
                'student_email' => 'john2.student@example.com',
                'phone' => '123456789',
                'role_names' => ['student'],
            ],
            [
                'email' => 'student3@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'John3',
                'last_name' => 'Doe3',
                'student_email' => 'john3.student@example.com',
                'phone' => '123456789',
                'role_names' => ['student'],
            ],
            [
                'email' => 'company@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'Alice',
                'last_name' => 'Company',
                'student_email' => 'alice.company@example.com',
                'phone' => '987654321',
                'role_names' => ['company'],
                'company_account_active_state' => 1,
                'company_name' => 'Company s.r.o',
            ],
            [
                'email' => 'company2@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'Alice2',
                'last_name' => 'Company2',
                'student_email' => 'alice2.company@example.com',
                'phone' => '987654321',
                'role_names' => ['company'],
                'company_account_active_state' => 1,
                'company_name' => 'Company2 s.r.o',
            ],
            [
                'email' => 'garant@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'Bob',
                'last_name' => 'Garant',
                'student_email' => 'bob.garant@example.com',
                'phone' => '111222333',
                'role_names' => ['garant'],
            ],
            [
                'email' => 'garant2@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'Bob2',
                'last_name' => 'Garant2',
                'student_email' => 'bob2.garant@example.com',
                'phone' => '111222333',
                'role_names' => ['garant'],
            ],
            [
                'email' => 'external@system.sk',
                'password' => bcrypt('ExtSystem123!'),
                'role_names' => ['external'],

                'first_name' => null,
                'last_name' => null,
                'student_email' => null,
                'phone' => null,
                'must_change_password' => 0,
            ],
            [
                'email' => 'admin@admin.sk',
                'password' => bcrypt('Admin123!'),
                'role_names' => ['admin'],

                'first_name' => null,
                'last_name' => null,
                'student_email' => null,
                'phone' => null,
                'must_change_password' => 0,
            ],
        ];

        foreach ($users as $userData) {
            // Vytvoríme základné údaje
            $userAttributes = [
                'email' => $userData['email'],
                'password' => $userData['password'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'student_email' => $userData['student_email'],
                'phone' => $userData['phone'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'must_change_password' => 0,
            ];

            // Ak má používateľ rolu "company", pridáme aj firemné údaje
            if (in_array('company', $userData['role_names'])) {
                $userAttributes['company_account_active_state'] = $userData['company_account_active_state'] ?? 0;
                $userAttributes['company_name'] = $userData['company_name'] ?? 'Neznáma firma';
            }

            // Vytvorenie používateľa
            $user = User::create($userAttributes);

            // Priradenie rolí
            $roles = Role::whereIn('name', $userData['role_names'])->get();
            $user->roles()->attach($roles);
        }
    }
}
