<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

/** Ajout d'une ordonnance à une consultation. */
class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'content'     => ['required', 'string'],
            'valid_until' => ['nullable', 'date_format:Y-m-d', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required'   => 'Le contenu de l\'ordonnance est obligatoire.',
            'valid_until.after'  => 'La date de validité doit être dans le futur.',
        ];
    }
}