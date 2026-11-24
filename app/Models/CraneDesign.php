<?php

namespace App\Models;

use Database\Factories\CraneDesignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraneDesign extends Model
{
    /** @use HasFactory<CraneDesignFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'crane_type',
        'capacity',
        'span',
        'lifting_height',
        'girder_depth',
        'girder_width',
        'end_carriage_length',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'span' => 'integer',
            'lifting_height' => 'integer',
            'girder_depth' => 'integer',
            'girder_width' => 'integer',
            'end_carriage_length' => 'integer',
        ];
    }
}
