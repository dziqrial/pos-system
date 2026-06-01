<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'in:retail,fnb,pharmacy'],
            'timezone'       => ['required', 'string', 'timezone'],
            'owner_name'     => ['required', 'string', 'max:255'],
            'owner_email'    => ['required', 'email', 'unique:users,email'],
            'owner_password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Nama toko wajib diisi.',
            'type.required'              => 'Tipe toko wajib dipilih.',
            'type.in'                    => 'Tipe toko tidak valid.',
            'timezone.required'          => 'Zona waktu wajib dipilih.',
            'timezone.timezone'          => 'Zona waktu tidak valid.',
            'owner_name.required'        => 'Nama pemilik wajib diisi.',
            'owner_email.required'       => 'Email pemilik wajib diisi.',
            'owner_email.email'          => 'Format email pemilik tidak valid.',
            'owner_email.unique'         => 'Email sudah digunakan.',
            'owner_password.required'    => 'Password wajib diisi.',
            'owner_password.min'         => 'Password minimal 8 karakter.',
            'owner_password.confirmed'   => 'Konfirmasi password tidak cocok.',
        ];
    }
}
