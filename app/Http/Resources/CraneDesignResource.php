<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CraneDesignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'crane_type' => $this->crane_type,
            'capacity' => (float) $this->capacity,
            'span' => $this->span,
            'lifting_height' => $this->lifting_height,
            'girder_depth' => $this->girder_depth,
            'girder_width' => $this->girder_width,
            'end_carriage_length' => $this->end_carriage_length,
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
