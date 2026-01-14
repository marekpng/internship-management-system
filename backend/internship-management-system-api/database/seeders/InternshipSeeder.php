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

        // Vytvoríme internships
        // stavy mame Vytvorená, Potvrdená, Schválená, Zamietnutá, Obhájená, Neobhájená
        Internship::create([
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'semester' => 'Zimný',
            'status' => 'Schválená',
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

        Internship::create([
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'semester' => 'Letný',
            'status' => 'Schválená',
            'year' => 2025,
            'company_id' => $company->id,
            'student_id' => $student->id,
            'garant_id' => $garant->id,
        ]);

        Internship::create([
            'start_date' => now()->addMonths(3),
            'end_date' => now()->addMonths(6),
            'semester' => 'Zimný',
            'status' => 'Potvrdená',
            'year' => 2026,
            'company_id' => $company->id,
            'student_id' => $student->id,
            'garant_id' => $garant->id,
        ]);
    }
}
