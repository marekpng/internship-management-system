<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Internship;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class InternshipSeeder extends Seeder
{
    public function run()
    {
        // Získanie existujúcich používateľov (firma, študent, garant)
        $company = User::whereHas('roles', function ($query) {
            $query->where('name', 'company');
        })->first();

        $student = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->first();

        $garant = User::whereHas('roles', function ($query) {
            $query->where('name', 'garant');
        })->first();

<<<<<<< HEAD
=======
        // // Skontrolujeme, či existujú, ak nie, môžeme ich vytvoriť (v prípade, že ich v databáze ešte nemáme)
        // if (!$company) {
        //     $company = User::create([
        //         'email' => 'company@example.com',
        //         'password' => Hash::make('password'),
        //         'must_change_password' => false,
        //         'company_name' => 'Tech Corp',
        //         'contact_person_name' => 'Michael Johnson',
        //         'contact_person_email' => 'michael@techcorp.com',
        //         'contact_person_phone' => '1122334455',
        //     ]);
        //     // Add 'company' role to this user
        //     $company->roles()->attach(Role::where('name', 'company')->first());
        // }

        // if (!$student) {
        //     $student = User::create([
        //         'email' => 'student@example.com',
        //         'password' => Hash::make('password'),
        //         'must_change_password' => false,
        //         'first_name' => 'John',
        //         'last_name' => 'Doe',
        //         'phone' => '123456789',
        //         'student_email' => 'john.student@example.com',
        //     ]);
        //     // Add 'student' role to this user
        //     $student->roles()->attach(Role::where('name', 'student')->first());
        // }

        // if (!$garant) {
        //     // Only create the user if it doesn't already exist
        //     if (!$garant) {
        //         $garant = User::create([
        //             'email' => 'garant@example.com',
        //             'password' => Hash::make('password'),
        //             'must_change_password' => false,
        //             'first_name' => 'Jane',
        //             'last_name' => 'Smith',
        //             'phone' => '987654321',
        //             'student_email' => 'jane.garant@example.com',
        //         ]);
        //         // Add 'garant' role to this user
        //         $garant->roles()->attach(Role::where('name', 'garant')->first());
        //     }
        // }

>>>>>>> origin/feature/student-fix
        // Vytvoríme internships
        // stavy mame Vytvorená, Potvrdená, Schválená, Zamietnutá, Obhájená, Neobhájená
        Internship::create([
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'semester' => 'Zimný',
            'status' => 'Vytvorená',
            'year' => 2025,
            'company_id' => $company->id,
            'student_id' => $student->id,
            'garant_id' => $garant->id,
        ]);

        Internship::create([
            'start_date' => now()->addMonths(3),
            'end_date' => now()->addMonths(6),
            'semester' => 'Letný',
            'status' => 'Vytvorená',
            'year' => 2026,
            'company_id' => $company->id,
            'student_id' => $student->id,
            'garant_id' => $garant->id,
        ]);
    }
}
