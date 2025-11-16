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
            'admin'
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
                'email' => 'garant@example.com',
                'password' => bcrypt('password'),
                'first_name' => 'Bob',
                'last_name' => 'Garant',
                'student_email' => 'bob.garant@example.com',
                'phone' => '111222333',
                'role_names' => ['garant'],
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
