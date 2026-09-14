<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Savings\CreateMemberSavingsAccountsAction;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterMemberAction
{
    public function __construct(
        private readonly CreateMemberSavingsAccountsAction $createSavingsAccounts,
    ) {}

    /**
     * @param  array{name: string, email: string, phone_number: string, password: string, nik: string, employee_nip: string, department: string, bank_name: string, bank_account_number: string, monthly_salary?: float|null}  $data
     */
    public function execute(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'password' => $data['password'],
                'role' => UserRole::MEMBER,
            ]);

            $member = $user->member()->create([
                'full_name' => $data['name'],
                'nik' => $data['nik'],
                'employee_nip' => $data['employee_nip'],
                'department' => $data['department'],
                'bank_name' => $data['bank_name'],
                'bank_account_number' => $data['bank_account_number'],
                'monthly_salary' => $data['monthly_salary'] ?? 0,
                'status' => MemberStatus::PENDING,
            ]);

            $this->createSavingsAccounts->execute($member);

            return $member;
        });
    }
}
