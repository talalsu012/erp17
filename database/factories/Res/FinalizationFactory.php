<?php

namespace Database\Factories\Res;

use App\Models\Res\Finalization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Finalization>
 */
class FinalizationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faculty_code' => 10,
            'department_code' => 100,
            'level_code' => 1,
            'batch_code' => 2026,
            'version' => 1,
            'kind' => Finalization::KIND_APPROVAL,
            'is_active' => true,
            'user_id' => User::factory(),
            'approved_at' => now(),
            'ip' => '127.0.0.1',
            'students_count' => 0,
            'marks_count' => 0,
            'digest' => str_repeat('0', 64),
            'signature' => str_repeat('0', 64),
        ];
    }

    public function amendment(): static
    {
        return $this->state(['kind' => Finalization::KIND_AMENDMENT]);
    }

    public function superseded(): static
    {
        return $this->state(['is_active' => false]);
    }
}
