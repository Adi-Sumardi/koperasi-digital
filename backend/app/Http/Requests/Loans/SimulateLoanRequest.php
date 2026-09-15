<?php

declare(strict_types=1);

namespace App\Http\Requests\Loans;

use Illuminate\Foundation\Http\FormRequest;

class SimulateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loan_product_id' => ['required', 'uuid', 'exists:loan_products,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'tenor_months' => ['required', 'integer', 'min:1'],
        ];
    }
}
