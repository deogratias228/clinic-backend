<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/** Mise à jour partielle d'un utilisateur. */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['sometimes', 'uuid', 'exists:roles,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $this->user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}