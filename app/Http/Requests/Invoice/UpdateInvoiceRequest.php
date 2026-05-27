<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour des métadonnées d'une facture (remise, notes, échéance). */
class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'discount' => ['sometimes', 'numeric', 'min:0'],
            'notes'    => ['nullable', 'string', 'max:1000'],
            'due_at'   => ['nullable', 'date'],
        ];
    }
}