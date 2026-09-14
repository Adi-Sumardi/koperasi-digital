<?php

declare(strict_types=1);

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepositSavingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->member !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['pokok', 'wajib', 'sukarela'])],
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }
}
