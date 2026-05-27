<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise une facture.
 * remaining_amount : accesseur calculé depuis total_amount - paid_amount - discount.
 * items et payments : chargés uniquement sur les routes de détail.
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'number'           => $this->number,
            'status'           => $this->status,
            'total_amount'     => (float) $this->total_amount,
            'paid_amount'      => (float) $this->paid_amount,
            'discount'         => (float) $this->discount,
            'remaining_amount' => (float) $this->remaining_amount,
            'currency'         => $this->currency,
            'notes'            => $this->notes,
            'issued_at'        => $this->issued_at?->toIso8601String(),
            'due_at'           => $this->due_at?->toIso8601String(),
            'patient'          => $this->whenLoaded('patient', fn () => PatientResource::make($this->patient)),
            'visit'            => $this->whenLoaded('visit',   fn () => VisitResource::make($this->visit)),
            'items'            => $this->whenLoaded('items',   fn () => InvoiceItemResource::collection($this->items)),
            'payments'         => $this->whenLoaded('payments',fn () => PaymentResource::collection($this->payments)),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
