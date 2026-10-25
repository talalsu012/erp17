<?php

namespace Database\Seeders;

use App\Models\Reg\Payment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * دفعة واحدة لكل طالب.
     */
    public function run(): void
    {
        Student::query()->each(function (Student $student): void {
            Payment::factory()->forStudent($student)->create();
        });
    }
}
