<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un rendez-vous.
 * patient_display : nom affiché — fiche patient si liée, sinon patient_name saisi en ligne.
 */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'appointment_date' => $this->appointment_date->toDateString(),
            'start_time'       => substr($this->start_time, 0, 5),
            'end_time'         => substr($this->end_time, 0, 5),
            'status'           => $this->status,
            'source'           => $this->source,
            'patient_name'     => $this->patient?->full_name ?? $this->patient_name,
            'patient_phone'    => $this->patient?->phone     ?? $this->patient_phone,
            'reason'           => $this->reason,
            'notes'            => $this->notes,
            'doctor'           => $this->whenLoaded('doctor', fn () => DoctorResource::make($this->doctor)),
            'patient'          => $this->whenLoaded('patient', fn () => PatientResource::make($this->patient)),
            'visit'            => $this->whenLoaded('visit',   fn () => VisitResource::make($this->visit)),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
