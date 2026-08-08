<?php

namespace Database\Seeders;

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
        /*
        |--------------------------------------------------------------------------
        | Default User
        |--------------------------------------------------------------------------
        */

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | ALYASI Seeders
        |--------------------------------------------------------------------------
        */

        $this->call([
            CommunityCategorySeeder::class,
            CommunityPostSeeder::class,
            TechnologySeeder::class,
            WorkDemoSeeder::class,
        ]);
    }
}
