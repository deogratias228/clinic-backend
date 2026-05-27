<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un dossier patient.
 * full_name et age sont des accesseurs du modèle.
 * visits et appointments : chargés uniquement sur la route show (dossier complet).
 */
class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'full_name'   => $this->full_name,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'birth_date'  => $this->birth_date?->toDateString(),
            'age'         => $this->age,
            'gender'      => $this->gender,
            'address'     => $this->address,
            'blood_type'  => $this->blood_type,
            'allergies'   => $this->allergies,
            'notes'       => $this->notes,
            'visits'      => $this->whenLoaded('visits', fn () => VisitResource::collection($this->visits)),
            'appointments'=> $this->whenLoaded('appointments', fn () => AppointmentResource::collection($this->appointments)),
            'created_at'  => $this->created_at->toDateString(),
        ];
    }
}
