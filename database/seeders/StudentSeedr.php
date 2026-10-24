<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StudentSeedr extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Student::factory()->count(5)->create();
    }
}
