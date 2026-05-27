<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour des surcharges de permissions individuelles d'un utilisateur.
 * overrides : tableau de { permission: string, granted: bool }
 */
class UpdateUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'overrides'              => ['required', 'array'],
            'overrides.*.permission' => ['required', 'string', 'exists:permissions,name'],
            'overrides.*.granted'    => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'overrides.*.permission.exists'  => 'Permission inconnue : :input',
            'overrides.*.granted.required'   => 'La valeur granted est obligatoire.',
        ];
    }
}