<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/** Création d'un utilisateur back-office. */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'role_id'   => ['required', 'uuid', 'exists:roles,id'],
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required'  => 'Le rôle est obligatoire.',
            'name.required'     => 'Le nom est obligatoire.',
            'email.required'    => 'L\'email est obligatoire.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}