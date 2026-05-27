<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour partielle d'un rendez-vous. */
class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'doctor_id'        => ['sometimes', 'uuid', 'exists:doctors,id'],
            'patient_id'       => ['nullable', 'uuid', 'exists:patients,id'],
            'appointment_date' => ['sometimes', 'date_format:Y-m-d'],
            'start_time'       => ['sometimes', 'date_format:H:i'],
            'end_time'         => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'status'           => ['sometimes', 'string', 'in:pending,confirmed,cancelled,no_show,done'],
            'reason'           => ['nullable', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}