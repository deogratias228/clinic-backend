<?php

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

/** Création d'une visite walk-in (sans rendez-vous préalable). */
class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'uuid', 'exists:doctors,id'],
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Le médecin est obligatoire.',
            'patient_id.required' => 'Le patient est obligatoire.',
        ];
    }
}