<?php

declare(strict_types=1);

namespace App\Http\Requests\Loans;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class RepayLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Loan $loan */
        $loan = $this->route('loan');

        return $loan->member_id === $this->user()?->member?->id;
    }

    public function rules(): array
    {
        return [];
    }
}
