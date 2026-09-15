<?php

declare(strict_types=1);

namespace App\Http\Requests\Loans;

use Illuminate\Foundation\Http\FormRequest;

class ApplyLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->member?->isActive() ?? false;
    }

    public function rules(): array
    {
        return [
            'loan_product_id' => ['required', 'uuid', 'exists:loan_products,id'],
            'amount' => ['required', 'numeric', 'min:500000'],
            'tenor_months' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string', 'max:500'],
            'guarantee_type' => ['nullable', 'string', 'in:payroll,bpjs,vehicle,property'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Pengajuan pinjaman minimal Rp 500.000.',
        ];
    }
}
