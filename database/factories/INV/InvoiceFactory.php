<?php

namespace Database\Factories\INV;

use App\Models\Invoice\Invoice;
use App\Models\Reg\Faculty;
use App\Reg\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabic = \Faker\Factory::create('ar_SA');

        return [
            'name_ar' => $arabic->name(),
            'invoice_no' => $this->faker->unique()->numerify('1000002602###'),
            'amount' => $this->faker->randomElement([5000, 10000, 25000, 60000]),
            'sys_type' => $this->faker->randomElement([4002, 4003, 4004]),
            'faculty_code' => Faculty::query()->inRandomOrder()->value('code'),
            'description' => $arabic->sentence(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->numerify('09########'),
            'bank_pay' => '0',
            'due_date' => now()->addDays(7),
            'expire_at' => now()->addDays(14),
            'status' => InvoiceStatus::pending,
        ];
    }
}
