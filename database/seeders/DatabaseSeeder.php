<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\LearningUnit;
use App\Models\Level;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::factory()->create(['mailAddress' => 'sahlan.royale@gmail.com']);
        Admin::factory()->create(['mailAddress' => 'mariaelqibthi@gmail.com']);

        User::factory()->count(5)->create();
    }
}
