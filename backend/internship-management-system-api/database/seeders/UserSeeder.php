<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Roly
        $roleNames = ['student', 'company', 'garant', 'admin', 'external'];
        foreach ($roleNames as $name) {
            Role::firstOrCreate(['name' => $name]);
        }

        $now = Carbon::now();

        // 2) Používatelia (2 študenti, 2 firmy, 2 garanti, admin, external)
        $users = [
            // -------------------------
            // STUDENTS (povinné polia podľa formu)
            // -------------------------
            [
                'email' => 'student1@example.com',
                'password' => bcrypt('password'),
                'role_names' => ['student'],

                'first_name' => 'Ján',
                'last_name' => 'Student',
                'street' => 'Hlavná',
                'house_number' => '12',
                'city' => 'Košice',
                'postal_code' => '04001',
                'student_email' => 'jan.novak@stuba.sk',
                'alternative_email' => 'jan.novak@gmail.com', // nepovinné, ale ok
                'phone' => '0901111222',
                'study_field' => 'Informatika',
            ],
            [
                'email' => 'student2@example.com',
                'password' => bcrypt('password'),
                'role_names' => ['student'],

                'first_name' => 'Petra',
                'last_name' => 'Studentova',
                'street' => 'Štúrova',
                'house_number' => '8A',
                'city' => 'Bratislava',
                'postal_code' => '81101',
                'student_email' => 'petra.kovacova@stuba.sk',
                'alternative_email' => 'petra.kovacova@gmail.com',
                'phone' => '0903333444',
                'study_field' => 'Softvérové inžinierstvo',
            ],

            // -------------------------
            // GARANTS
            // -------------------------
            [
                'email' => 'garant1@example.com',
                'password' => bcrypt('password'),
                'role_names' => ['garant'],

                'first_name' => 'Marek',
                'last_name' => 'Garant',
                'phone' => '0905555666',
                'alternative_email' => 'marek.horvath@uniza.sk',
            ],
            [
                'email' => 'garant2@example.com',
                'password' => bcrypt('password'),
                'role_names' => ['garant'],

                'first_name' => 'Lucia',
                'last_name' => 'Garantova',
                'phone' => '0907777888',
                'alternative_email' => 'lucia.urbanova@uniba.sk',
            ],

            // -------------------------
            // COMPANIES (povinné polia podľa formu)
            // -------------------------
            [
                'email' => 'company1@example.com',
                'password' => bcrypt('password'),
                'role_names' => ['company'],

                'company_account_active_state' => 1,

                'company_name' => 'TechNova s.r.o.',
                'ico' => '12345678',
                'street' => 'Priemyselná',
                'house_number' => '5',
                'city' => 'Žilina',
                'postal_code' => '01001',

                'contact_person_name' => 'Tomáš Králik',
                'contact_person_email' => 'tomas.kralik@technova.sk',
                'contact_person_phone' => '0911222333',

                // niekde v appke môžeš používať aj "phone"
                'phone' => '0911222333',
            ],
            [
                'email' => 'company2@example.com',
                'password' => bcrypt('password'),
                'role_names' => ['company'],

                'company_account_active_state' => 1,

                'company_name' => 'SoftWorks a.s.',
                'ico' => '87654321',
                'street' => 'Námestie slobody',
                'house_number' => '1',
                'city' => 'Trnava',
                'postal_code' => '91701',

                'contact_person_name' => 'Eva Šimková',
                'contact_person_email' => 'eva.simkova@softworks.sk',
                'contact_person_phone' => '0911444555',

                'phone' => '0911444555',
            ],

            // -------------------------
            // EXTERNAL (nechaj tak ako je)
            // -------------------------
            [
                'email' => 'external@system.sk',
                'password' => bcrypt('ExtSystem123!'),
                'role_names' => ['external'],
                'must_change_password' => 0,
            ],

            // -------------------------
            // ADMIN (nechaj tak ako je)
            // -------------------------
            [
                'email' => 'admin@admin.sk',
                'password' => bcrypt('Admin123!'),
                'role_names' => ['admin'],
                'must_change_password' => 0,
            ],
        ];

        foreach ($users as $u) {
            $roleNames = $u['role_names'] ?? [];
            unset($u['role_names']);

            // Defaults
            $u['must_change_password'] = $u['must_change_password'] ?? 0;
            $u['notify_new_request'] = $u['notify_new_request'] ?? true;
            $u['notify_approved'] = $u['notify_approved'] ?? true;
            $u['notify_rejected'] = $u['notify_rejected'] ?? true;
            $u['notify_profile_change'] = $u['notify_profile_change'] ?? true;

            $u['created_at'] = $now;
            $u['updated_at'] = $now;

            // Update/Create podľa emailu
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );

            // Sync roles
            $roles = Role::whereIn('name', $roleNames)->get();
            $user->roles()->sync($roles->pluck('id')->all());
        }
    }
}
