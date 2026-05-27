<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour partielle d'une consultation. */
class UpdateConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symptoms' => ['nullable', 'string', 'max:3000'],
            'diagnosis' => ['nullable', 'string', 'max:3000'],
            'treatment' => ['nullable', 'string', 'max:3000'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'follow_up' => ['nullable', 'string', 'max:100'],
        ];
    }
}