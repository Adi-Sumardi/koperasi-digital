<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SubmitKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->member !== null;
    }

    public function rules(): array
    {
        return [
            'ktp_photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'selfie_ktp' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
