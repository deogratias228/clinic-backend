<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Sérialise une ligne de facture. */
class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'label'          => $this->label,
            'quantity'       => $this->quantity,
            'unit_price'     => (float) $this->unit_price,
            'total'          => (float) $this->total,
            'sort_order'     => $this->sort_order,
            'act_catalog'    => $this->whenLoaded('actCatalog', fn () => ActCatalogResource::make($this->actCatalog)),
        ];
    }
}
