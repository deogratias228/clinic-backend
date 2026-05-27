<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un paiement.
 * processed_by : caissier qui a enregistré le paiement.
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'amount'       => (float) $this->amount,
            'method'       => $this->method,
            'reference'    => $this->reference,
            'notes'        => $this->notes,
            'paid_at'      => $this->paid_at->toIso8601String(),
            'processed_by' => $this->whenLoaded('processedBy', fn () => [
                'id'   => $this->processedBy->id,
                'name' => $this->processedBy->name,
            ]),
        ];
    }
}
