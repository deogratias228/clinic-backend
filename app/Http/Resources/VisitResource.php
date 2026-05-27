<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise une visite.
 * waiting_minutes et consultation_minutes : accesseurs calculés du modèle.
 */
class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'status'                => $this->status,
            'reason'                => $this->reason,
            'arrived_at'            => $this->arrived_at?->toIso8601String(),
            'seen_at'               => $this->seen_at?->toIso8601String(),
            'done_at'               => $this->done_at?->toIso8601String(),
            'waiting_minutes'       => $this->waiting_minutes,
            'consultation_minutes'  => $this->consultation_minutes,
            'doctor'                => $this->whenLoaded('doctor',       fn () => DoctorResource::make($this->doctor)),
            'patient'               => $this->whenLoaded('patient',      fn () => PatientResource::make($this->patient)),
            'appointment'           => $this->whenLoaded('appointment',  fn () => AppointmentResource::make($this->appointment)),
            'consultation'          => $this->whenLoaded('consultation', fn () => ConsultationResource::make($this->consultation)),
            'invoice'               => $this->whenLoaded('invoice',      fn () => InvoiceResource::make($this->invoice)),
            'created_at'            => $this->created_at->toIso8601String(),
        ];
    }
}
