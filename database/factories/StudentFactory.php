<?php

namespace Database\Factories;

use App\Models\Reg\Campus;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fakerAr = \Faker\Factory::create('ar_SA');

        $universityNo = $this->faker->unique()->numberBetween(1000000, 9999999);

        return [
            'university_no' => $universityNo,
            'name_ar' => $fakerAr->name('male'),
            'name_en' => $this->faker->name(),
            'first_name' => $fakerAr->firstName(),
            'second_name' => $fakerAr->firstName(),
            'third_name' => $fakerAr->firstName(),
            'fourth_name' => $fakerAr->lastName(),

            'faculty_code' => Faculty::inRandomOrder()->first()->code,
            'level_code' => Level::inRandomOrder()->first()->code,
            'campus_code' => Campus::inRandomOrder()->first()->code,
            'department_code' => Department::inRandomOrder()->first()->code,
            'stady_code' => LtColumn::where('group_slug', 'stady_code')->inRandomOrder()->first()->code,
            'admission_code' => LtColumn::where('group_slug', 'admission_type')->inRandomOrder()->first()->code,
            'academic_year_code' => LtColumn::where('group_slug', 'academic_year')->inRandomOrder()->first()->code,
            'status_code' => LtColumn::where('group_slug', 'x_status')->inRandomOrder()->first()->code,
            'batch_code' => LtColumn::where('group_slug', 'academic_year')->inRandomOrder()->first()->code,
            'gender_code' => LtColumn::where('group_slug', 'gender')->inRandomOrder()->first()->code,
            'installment_code' => LtColumn::where('group_slug', 'installment_code')->inRandomOrder()->first()->code,
            'nationality_code' => LtColumn::where('group_slug', 'nationality')->inRandomOrder()->first()->code,
            'nationaltyno' => $this->faker->unique()->numerify('##########'),
            'id_photo' => null,
            // 'gender_code' => $this->faker->numberBetween(1, 2),
            'birth_place' => $fakerAr->city(),
            'birth_date' => $this->faker->date(),
            'photo' => null,
            // 'nationality_code' => $this->faker->numberBetween(1, 10),
            'state_code' => LtColumn::where('group_slug', 'states')->inRandomOrder()->first()->code,
            'city' => $fakerAr->city(),
            'localty' => $fakerAr->streetName(),
            'block' => $this->faker->buildingNumber(),
            'home_no' => $this->faker->buildingNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'mobile' => $this->faker->phoneNumber(),
            'social_media' => null,
            'activites' => null,
            'skill' => null,
            'parent' => $fakerAr->name('male'),
            'parent_alive' => $this->faker->boolean(),
            'parent_job' => $this->faker->jobTitle(),
            'parent_address' => $this->faker->address(),
            'parent_contacts' => $this->faker->phoneNumber(),
            'mother' => $fakerAr->name('female'),
            'mother_alive' => $this->faker->boolean(),
            'mother_job' => $fakerAr->jobTitle(),
            'mother_address' => $fakerAr->address(),
            'mother_contacts' => $this->faker->phoneNumber(),
            'brothers' => $this->faker->numberBetween(0, 5),
            'currency' => $this->faker->randomElement(['SDG', 'USD']),
            // 'installment_code' => $this->faker->numberBetween(1, 10),
            'discount_percent' => $this->faker->numberBetween(0, 50),
            'fees' => $this->faker->randomFloat(2, 1000, 5000),
            'reg_fees' => $this->faker->randomFloat(2, 100, 500),
            'fees1' => 0,
            'fees2' => 0,
            'fees3' => 0,
            'fees4' => 0,
            'fees5' => 0,
            'fees6' => 0,
            'fees7' => 0,
            'fees8' => 0,
            'fees9' => 0,
            'fees10' => 0,
            'pay1' => 0,
            'pay2' => 0,
            'pay3' => 0,
            'pay4' => 0,
            'all_fees' => 0,
            'pay' => 0,
            'xmod' => 0,
            'xstatus' => 1001,
            'indexno' => $this->faker->numerify('STU-####'),
            'note' => $fakerAr->paragraph(),
            'is_visible' => true,
        ];
    }
}
