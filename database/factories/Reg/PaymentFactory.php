<?php

namespace Database\Factories\Reg;

use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            // جلب طالب عشوائي للتأكد من صحة العلاقة
            'university_no' => Student::query()->inRandomOrder()->value('university_no')
                                ?? $this->faker->numberBetween(100000, 999999),

            'amount' => $this->faker->randomFloat(2, 5000, 150000),

            'currency' => $this->faker->randomElement(['SDG', 'USD', 'SAR']),

            'bankdate' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),

            'bank' => $this->faker->randomElement(['BOK', 'FIB', 'LOC', 'OIB']),

            'branch' => $this->faker->city().' Branch',

            'voucher_no' => 'VCH-'.$this->faker->unique()->numberBetween(1000, 99999),

            // جلب كود الدفعة من LtColumn بناءً على الـ group_slug الخاص بك
            'batch_code' => LtColumn::where('grouped', '2003')->inRandomOrder()->value('code'),

            // جلب كود المستوى
            'level_code' => Level::query()->inRandomOrder()->value('code'),

            'sys_type' => $this->faker->randomElement(['Online', 'Counter', 'Transfer']),

            'created_at' => now(),
            'updated_at' => now(),

        ];
    }
}
