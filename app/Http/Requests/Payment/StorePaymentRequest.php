<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Enregistrement d'un paiement sur une facture.
 * method : cash | mobile_money | card | insurance | transfer.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', 'string', 'in:cash,mobile_money,card,insurance,transfer'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes'     => ['nullable', 'string', 'max:500'],
            'paid_at'   => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'  => 'Le montant est obligatoire.',
            'amount.min'       => 'Le montant doit être supérieur à 0.',
            'method.required'  => 'Le mode de paiement est obligatoire.',
            'method.in'        => 'Mode de paiement invalide.',
        ];
    }
}