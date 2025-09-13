<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherKeyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'key_code' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];

        // Add unique rule for key_code on creation
        if ($this->isMethod('POST')) {
            $rules['key_code'][] = 'unique:teacher_keys,key_code';
        }

        // Modify unique rule for updates to ignore current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['key_code'][] = 'unique:teacher_keys,key_code,' . $this->route('key')->id;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key_code.unique' => 'This QR code is already in use.',
            'expires_at.after' => 'The expiration date must be in the future.',
        ];
    }
}
