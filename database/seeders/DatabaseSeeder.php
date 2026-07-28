<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\CategorySeeder;
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
        // Only create demo user in local/dev environments, never in production
        if (!app()->environment('production')) {
            User::firstOrCreate(
                ['email' => 'demo@finance.app'],
                [
                    'name' => 'Demo User',
                    'password' => bcrypt('password'),
                ]
            );
        }

        $this->call([
            CategorySeeder::class,
        ]);
    }
}
