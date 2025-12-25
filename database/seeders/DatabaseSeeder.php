<?php

namespace Database\Seeders;

use App\Models\ProfileWeb;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('adminmagang'),
        ]);

        ProfileWeb::create([
            'app_name' => 'titik magang',
            'description' => 'sdna',
            'logo_path' => 'logos/logo.png',
        ]);
    }
}
