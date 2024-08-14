<?php

namespace Database\Factories;

use App\Models\LearningUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class LearningUnitFactory extends Factory
{
    protected $model = LearningUnit::class;
    
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
            ]),
        ];
    }
}
