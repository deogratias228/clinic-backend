<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

/** Création d'un médecin. */
class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'                   => ['nullable', 'uuid', 'exists:users,id'],
            'specialty_id'              => ['nullable', 'uuid', 'exists:specialties,id'],
            'name'                      => ['required', 'string', 'max:150'],
            'phone'                     => ['nullable', 'string', 'max:20'],
            'bio'                       => ['nullable', 'string', 'max:2000'],
            'photo'                     => ['nullable', 'image', 'max:2048'],
            'slot_duration_minutes'     => ['sometimes', 'integer', 'in:15,20,30,45,60'],
            'accepts_online_booking'    => ['sometimes', 'boolean'],
            'uses_consultation_module'  => ['sometimes', 'boolean'],
            'is_active'                 => ['sometimes', 'boolean'],
            'sort_order'                => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du médecin est obligatoire.',
        ];
    }
}