<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un médecin.
 * photo_url : URL publique construite depuis photo_path.
 * schedules : chargés uniquement sur les routes de détail.
 */
class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'photo_url' => $this->photo_path
                ? asset('storage/' . $this->photo_path)
                : null,
            'slot_duration_minutes' => $this->slot_duration_minutes,
            'accepts_online_booking' => $this->accepts_online_booking,
            'uses_consultation_module' => $this->uses_consultation_module,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'specialty' => $this->whenLoaded('specialty', fn() => SpecialtyResource::make($this->specialty)),
            'schedules' => $this->whenLoaded('schedules', fn() => ScheduleResource::collection($this->schedules)),
            'user' => $this->whenLoaded('user', fn() => UserResource::make($this->user)),
        ];
    }
}
