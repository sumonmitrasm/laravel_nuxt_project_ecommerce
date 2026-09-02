<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $this->input('name')));

        $this->merge([
            'name' => $name,
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:20',
                "regex:/^[\p{L}\p{M}]+(?:[ .'-][\p{L}\p{M}]+)*$/u",
            ],
            'email' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:72',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The full name may contain only letters, spaces, apostrophes, hyphens, and dots.',
            'name.max' => 'The full name must not be greater than 20 characters.',
            'password.max' => 'The password must not be greater than 72 characters.',
        ];
    }
}
