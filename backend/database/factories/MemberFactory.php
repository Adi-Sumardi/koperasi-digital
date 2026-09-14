<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'nik' => fake()->unique()->numerify('################'),
            'employee_nip' => fake()->unique()->numerify('NIP-######'),
            'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Operations']),
            'bank_name' => fake()->randomElement(['BCA', 'BNI', 'BRI', 'Mandiri']),
            'bank_account_number' => fake()->numerify('##########'),
            'monthly_salary' => fake()->numberBetween(5_000_000, 20_000_000),
            'status' => MemberStatus::PENDING,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MemberStatus::ACTIVE,
            'member_number' => 'KOP-'.now()->year.'-'.fake()->unique()->numerify('#####'),
            'joined_at' => now(),
        ]);
    }
}
