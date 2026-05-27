<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ajout d'une ligne à une facture.
 * act_catalog_id nullable : ligne manuelle sans référence au catalogue.
 */
class StoreInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'act_catalog_id' => ['nullable', 'uuid', 'exists:act_catalog,id'],
            'label'          => ['required', 'string', 'max:255'],
            'quantity'       => ['required', 'integer', 'min:1', 'max:999'],
            'unit_price'     => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required'     => 'Le libellé est obligatoire.',
            'quantity.required'  => 'La quantité est obligatoire.',
            'unit_price.required'=> 'Le prix unitaire est obligatoire.',
        ];
    }
}