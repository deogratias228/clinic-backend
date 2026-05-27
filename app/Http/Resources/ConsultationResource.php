<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise une consultation médicale.
 * prescriptions : chargées uniquement sur les routes de détail.
 */
class ConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'symptoms'      => $this->symptoms,
            'diagnosis'     => $this->diagnosis,
            'treatment'     => $this->treatment,
            'notes'         => $this->notes,
            'follow_up'     => $this->follow_up,
            'consulted_at'  => $this->consulted_at->toIso8601String(),
            'doctor'        => $this->whenLoaded('doctor',        fn () => DoctorResource::make($this->doctor)),
            'prescriptions' => $this->whenLoaded('prescriptions', fn () => PrescriptionResource::collection($this->prescriptions)),
            'updated_at'    => $this->updated_at->toIso8601String(),
        ];
    }
}
