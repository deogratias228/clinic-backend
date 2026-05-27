<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Prise de rendez-vous en ligne par le patient (sans compte).
 * end_time calculable côté client depuis start_time + slot_duration_minutes.
 */
class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_name'     => ['required', 'string', 'max:150'],
            'patient_phone'    => ['required', 'string', 'max:20'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time'       => ['required', 'date_format:H:i'],
            'end_time'         => ['required', 'date_format:H:i', 'after:start_time'],
            'reason'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_name.required'          => 'Votre nom complet est obligatoire.',
            'patient_phone.required'         => 'Votre numéro de téléphone est obligatoire.',
            'appointment_date.required'      => 'La date est obligatoire.',
            'appointment_date.after_or_equal'=> 'La date ne peut pas être dans le passé.',
            'start_time.required'            => 'L\'heure de début est obligatoire.',
            'end_time.after'                 => 'L\'heure de fin doit être après l\'heure de début.',
        ];
    }
}