<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeedr extends Seeder
{
    public const STUDENTS_COUNT = 50;

    public function run(): void
    {
        Student::factory()->count(self::STUDENTS_COUNT)->create();
    }
}
