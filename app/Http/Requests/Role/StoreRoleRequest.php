<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

/** Création d'un rôle personnalisé. */
class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:50', 'unique:roles,name', 'regex:/^[a-z_]+$/'],
            'label'          => ['required', 'string', 'max:100'],
            'is_default'     => ['sometimes', 'boolean'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*'=> ['uuid', 'exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le slug du rôle est obligatoire.',
            'name.unique'   => 'Ce slug est déjà utilisé.',
            'name.regex'    => 'Le slug ne peut contenir que des lettres minuscules et underscores.',
            'label.required'=> 'Le libellé est obligatoire.',
        ];
    }
}