<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\LearningUnit;
use App\Models\Level;
use App\Models\Question;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin::factory()->create(['mailAddress' => 'sahlan.royale@gmail.com']);
        // Admin::factory()->create(['mailAddress' => 'mariaelqibthi@gmail.com']);

        // User::factory()->count(5)->create();

        for($i = 1; $i <= 3; $i++) {
            Question::factory()->create([
                'question' => 'sample question ' . $i,
                'answer' => 'c|answer ' . $i,
                'type' => 'Multiple Choice',
                'levelId' => 6
            ]);
        }

        for($i = 1; $i <= 3; $i++) {
            Question::factory()->create([
                'question' => 'sample question ' . $i,
                'answer' => 'c|answer ' . $i,
                'type' => 'Multiple Choice',
                'levelId' => 7
            ]);
        }
    }
}
