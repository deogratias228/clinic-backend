<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour partielle d'un dossier patient. */
class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}