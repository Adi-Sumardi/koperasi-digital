<?php

declare(strict_types=1);

namespace App\Http\Requests\Loans;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role;

        return in_array($role, [UserRole::TREASURER, UserRole::CHAIRMAN, UserRole::SUPERADMIN], true);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
