<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création manuelle d'un rendez-vous depuis le back-office.
 * patient_id ou patient_name/patient_phone requis.
 */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'doctor_id'        => ['required', 'uuid', 'exists:doctors,id'],
            'patient_id'       => ['nullable', 'uuid', 'exists:patients,id'],
            'patient_name'     => ['nullable', 'string', 'max:150'],
            'patient_phone'    => ['nullable', 'string', 'max:20'],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'start_time'       => ['required', 'date_format:H:i'],
            'end_time'         => ['required', 'date_format:H:i', 'after:start_time'],
            'status'           => ['nullable', 'string', 'in:pending,confirmed,cancelled,no_show,done'],
            'reason'           => ['nullable', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}