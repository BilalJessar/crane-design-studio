<?php

namespace Database\Factories;

use App\Models\CraneDesign;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CraneDesign> */
class CraneDesignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->bothify('Design ??-####'),
            'crane_type' => 'single_girder',
            'capacity' => 5,
            'span' => 15000,
            'lifting_height' => 6000,
            'girder_depth' => 500,
            'girder_width' => 300,
            'end_carriage_length' => 2500,
        ];
    }
}
