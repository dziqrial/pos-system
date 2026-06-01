<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,' . $userId],
            'password'  => [$this->isMethod('POST') ? 'required' : 'nullable', 'min:8', 'confirmed'],
            'role_id'   => ['nullable', 'exists:roles,id'],
            'is_active' => ['boolean'],
            'pin'       => ['nullable', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role_id.exists'     => 'Peran tidak valid.',
            'pin.digits'         => 'PIN harus 6 digit angka.',
        ];
    }
}
