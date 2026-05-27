<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Sérialise une indisponibilité ponctuelle. */
class UnavailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'date'        => $this->date->toDateString(),
            'start_time'  => $this->start_time ? substr($this->start_time, 0, 5) : null,
            'end_time'    => $this->end_time   ? substr($this->end_time, 0, 5)   : null,
            'is_full_day' => $this->isFullDay(),
            'reason'      => $this->reason,
        ];
    }
}
