<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Synchronisation complète des horaires d'un médecin.
 * Remplace tous les horaires existants par la nouvelle liste.
 */
class SyncSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'schedules.required' => 'Au moins un horaire est requis.',
            'schedules.*.day_of_week.in' => 'Jour de semaine invalide.',
            'schedules.*.start_time.required' => 'L\'heure de début est obligatoire.',
            'schedules.*.end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
        ];
    }
}