<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Sérialise une spécialité. */
class SpecialtyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'color'      => $this->color,
            'sort_order' => $this->sort_order,
        ];
    }
}
