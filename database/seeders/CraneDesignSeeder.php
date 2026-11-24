<?php

namespace Database\Seeders;

use App\Models\CraneDesign;
use Illuminate\Database\Seeder;

class CraneDesignSeeder extends Seeder
{
    public function run(): void
    {
        // Illustrative drawing inputs for development.
        $samples = [
            'Crane 001' => [
                'capacity' => 5, 'span' => 15000, 'lifting_height' => 6000,
                'girder_depth' => 500, 'girder_width' => 300, 'end_carriage_length' => 2500,
            ],
            'Crane 002' => [
                'capacity' => 3.25, 'span' => 10000, 'lifting_height' => 4500,
                'girder_depth' => 400, 'girder_width' => 250, 'end_carriage_length' => 2000,
            ],
            'Crane 003' => [
                'capacity' => 8, 'span' => 18000, 'lifting_height' => 7500,
                'girder_depth' => 650, 'girder_width' => 350, 'end_carriage_length' => 3000,
            ],
        ];

        foreach ($samples as $name => $attributes) {
            CraneDesign::firstOrCreate(['name' => $name], $attributes);
        }
    }
}
