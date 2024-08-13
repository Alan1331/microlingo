<?php

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class LevelFactory extends Factory
{
    protected $model = Level::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic' => $this->faker->randomElement([
                'Topic 1',
                'Topic 2',
                'Topic 3',
                'Topic 4',
                'Topic 5',
            ]),
        ];
    }
}
