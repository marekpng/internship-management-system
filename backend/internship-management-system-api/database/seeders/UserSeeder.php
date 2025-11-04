<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role; // Importujeme model Role

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
            Role::firstOrCreate([
                'name' => $role,
            ]);
        }

        // Vytvorenie používateľov
        $users = [
            [
                'email' => 'student@example.com',
                'password' => bcrypt('password123'),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'student_email' => 'john.student@example.com',
                'phone' => '123456789',
                'role_names' => ['student'], // Rola používateľa
            ],
            [
                'email' => 'company@example.com',
                'password' => bcrypt('password123'),
                'first_name' => 'Alice',
                'last_name' => 'Company',
                'student_email' => 'alice.company@example.com',
                'phone' => '987654321',
                'role_names' => ['company'], // Rola používateľa
            ],
            [
                'email' => 'garant@example.com',
                'password' => bcrypt('password123'),
                'first_name' => 'Bob',
                'last_name' => 'Garant',
                'student_email' => 'bob.garant@example.com',
                'phone' => '111222333',
                'role_names' => ['garant'], // Rola používateľa
            ],
        ];

        foreach ($users as $userData) {
            // Vytvárame používateľa
            $user = User::create([
                'email' => $userData['email'],
                'password' => $userData['password'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'student_email' => $userData['student_email'],
                'phone' => $userData['phone'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Priradíme roly používateľovi
            $roles = Role::whereIn('name', $userData['role_names'])->get();
            $user->roles()->attach($roles);
        }
    }
}
