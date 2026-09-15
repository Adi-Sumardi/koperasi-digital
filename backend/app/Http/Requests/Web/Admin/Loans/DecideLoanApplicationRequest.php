<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Admin\Loans;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
