<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\Member;
use App\Support\Nik;
use Illuminate\Foundation\Http\FormRequest;

class RegisterMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'regex:/^\+62[0-9]{9,13}$/', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nik' => [
                'required',
                'digits:16',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Member::where('nik_hash', Nik::hash($value))->exists()) {
                        $fail('NIK ini sudah terdaftar sebagai anggota koperasi.');
                    }
                },
            ],
            'employee_nip' => ['required', 'string', 'max:64', 'unique:members,employee_nip'],
            'department' => ['required', 'string', 'max:128'],
            'bank_name' => ['required', 'string', 'max:64'],
            'bank_account_number' => ['required', 'string', 'max:64'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Nomor HP wajib berformat internasional, contoh: +6281234567890.',
            'nik.digits' => 'NIK wajib terdiri dari 16 digit angka.',
        ];
    }
}
