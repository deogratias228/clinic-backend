<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour partielle d'un médecin. */
class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'specialty_id' => ['nullable', 'uuid', 'exists:specialties,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'slot_duration_minutes' => ['sometimes', 'integer', 'in:15,20,30,45,60'],
            'accepts_online_booking' => ['sometimes', 'boolean'],
            'uses_consultation_module' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}