<?php

namespace Database\Factories\Reg;

use App\Models\Reg\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bankDate = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'university_no' => Student::factory(),
            'amount' => $this->faker->randomElement([60000, 110000, 260000, 500000]),
            'currency' => 'SDG',
            'bankdate' => $bankDate->format('Y-m-d'),
            'bank' => $this->faker->randomElement(['FCB', 'ONB', 'NIL', 'FIB']),
            'branch' => null,
            'voucher_no' => $this->faker->unique()->numerify('##################'),
            'transaction_id' => null,
            'batch_code' => null,
            'level_code' => null,
            'sys_type' => null,
            'created_at' => $bankDate,
        ];
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (): array => [
            'university_no' => $student->university_no,
            'batch_code' => $student->batch_code,
            'level_code' => $student->level_code,
            'amount' => $student->all_fees > 0 ? $student->all_fees : $this->faker->randomElement([60000, 110000, 260000]),
        ]);
    }
}
