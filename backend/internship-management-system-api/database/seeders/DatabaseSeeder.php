<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder; // Správna cesta k RoleSeeder
use Database\Seeders\UserSeeder;
use Database\Seeders\InternshipSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Zavoláme rôzne seedery
        $this->call([
            RoleSeeder::class,        // Seedujeme roly
            UserSeeder::class,        // Seedujeme používateľov
            InternshipSeeder::class,  // Seedujeme internshipy
        ]);
    }
}
