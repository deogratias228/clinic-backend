<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour du libellé et des permissions d'un rôle. */
class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label'          => ['sometimes', 'string', 'max:100'],
            'is_default'     => ['sometimes', 'boolean'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*'=> ['uuid', 'exists:permissions,id'],
        ];
    }
}