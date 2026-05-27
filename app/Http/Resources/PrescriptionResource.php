<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Sérialise une ordonnance. */
class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'content'     => $this->content,
            'valid_until' => $this->valid_until?->toDateString(),
            'is_printed'  => $this->is_printed,
            'created_at'  => $this->created_at->toIso8601String(),
        ];
    }
}
