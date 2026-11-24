<?php

// Provisional application limits. Capacity is in tonnes and dimensions are in millimeters.
return [
    'name_max_length' => 150,
    'crane_types' => ['single_girder'],
    'capacity' => ['min' => 0.5, 'max' => 20, 'decimal_places' => 2],
    'dimensions' => [
        'span' => ['min' => 3000, 'max' => 30000],
        'lifting_height' => ['min' => 1000, 'max' => 20000],
        'girder_depth' => ['min' => 100, 'max' => 2000],
        'girder_width' => ['min' => 100, 'max' => 1500],
        'end_carriage_length' => ['min' => 500, 'max' => 6000],
    ],
];
