<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => "Koji Xenpai",
            'email' => 'koji@gmail.com',
            'password' => bcrypt('koji1234'),
        ]);
        $this->call(ThemeSeeder::class);
    }
}
