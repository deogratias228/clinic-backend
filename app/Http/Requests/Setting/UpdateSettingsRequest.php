<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour des paramètres du cabinet.
 * settings : objet clé-valeur — chaque clé correspond à une clé de la table settings.
 */
class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'settings'   => ['required', 'array', 'min:1'],
            'settings.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Au moins un paramètre est requis.',
        ];
    }
}