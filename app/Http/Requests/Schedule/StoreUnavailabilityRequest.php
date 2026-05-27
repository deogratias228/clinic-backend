<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ajout d'une indisponibilité ponctuelle.
 * start_time et end_time tous les deux null = journée entière bloquée.
 * start_time et end_time requis ensemble si l'un est fourni.
 */
class StoreUnavailabilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date'       => ['required', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time'   => ['nullable', 'date_format:H:i', 'after:start_time', 'required_with:start_time'],
            'reason'     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'             => 'La date est obligatoire.',
            'end_time.after'            => 'L\'heure de fin doit être après l\'heure de début.',
            'start_time.required_with'  => 'L\'heure de début est requise si une heure de fin est fournie.',
            'end_time.required_with'    => 'L\'heure de fin est requise si une heure de début est fournie.',
        ];
    }
}