<?php

namespace Database\Factories;

use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\Subject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    private const GENDER_MALE = 10010001;

    private const STATUS_ACTIVE = 20040001;

    private const X_STATUS_UNREGISTERED = 20070003;

    private const INSTALLMENT_FULL = 20050001;

    private const NO_DISCOUNT = 2008000;

    /**
     * يتطلب تشغيل SettingsSeeder أولاً: الكلية والقسم والمستوى تؤخذ من مقرر فعلي حتى تتسق مع جدول المقررات.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabic = \Faker\Factory::create('ar_SA');
        $subject = $this->randomCohortSubject();
        $gender = $this->randomCode('gender');
        $isMale = (int) $gender === self::GENDER_MALE;
        $batchCode = $this->randomCode('academic_year');
        $names = [
            $arabic->firstName($isMale ? 'male' : 'female'),
            $arabic->firstName('male'),
            $arabic->firstName('male'),
            $arabic->lastName(),
        ];
        $fees = $this->faker->randomElement([42500, 61250, 120000, 260000]);
        $regFees = 50000;
        $fees1 = 10000;

        return [
            'university_no' => $this->faker->unique()->numerify('2#########'),
            'name_ar' => implode(' ', $names),
            'name_en' => $this->faker->name($isMale ? 'male' : 'female'),
            'first_name' => $names[0],
            'second_name' => $names[1],
            'third_name' => $names[2],
            'fourth_name' => $names[3],

            'campus_code' => Faculty::query()->whereKey($subject->faculty_code)->value('campus_code'),
            'faculty_code' => $subject->faculty_code,
            'department_code' => $subject->department_code,
            'special_code' => Special::query()->whereKey($subject->special_code)->value('code'),
            'level_code' => $subject->level_code,
            'admission_code' => $this->randomCode('admission_type'),
            'stady_code' => $this->faker->randomElement([20060001, 20060002]),
            'academic_year_code' => $batchCode,
            'status_code' => self::STATUS_ACTIVE,
            'batch_code' => $batchCode,
            'gender_code' => $gender,
            'nationality_code' => $this->randomCode('nationality'),
            'state_code' => $this->randomCode('states'),
            'installment_code' => self::INSTALLMENT_FULL,

            'nationaltyno' => $this->faker->unique()->numerify('###########'),
            'birth_place' => $arabic->city(),
            'birth_date' => $this->faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'city' => $arabic->city(),
            'localty' => $arabic->streetName(),
            'block' => $this->faker->buildingNumber(),
            'home_no' => $this->faker->buildingNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'mobile' => $this->faker->numerify('09########'),

            'parent' => $arabic->name('male'),
            'parent_alive' => true,
            'parent_job' => $arabic->jobTitle(),
            'parent_address' => $arabic->address(),
            'parent_contacts' => $this->faker->numerify('09########'),
            'mother' => $arabic->name('female'),
            'mother_alive' => true,
            'mother_job' => $arabic->jobTitle(),
            'mother_address' => $arabic->address(),
            'mother_contacts' => $this->faker->numerify('09########'),
            'brothers' => (string) $this->faker->numberBetween(0, 5),

            'currency' => 'SDG',
            'discount_percent' => self::NO_DISCOUNT,
            'fees' => $fees,
            'reg_fees' => $regFees,
            'fees1' => $fees1,
            'all_fees' => $fees + $regFees + $fees1,
            'xmod' => $fees + $regFees + $fees1,
            'xstatus' => self::X_STATUS_UNREGISTERED,
            'indexno' => $this->faker->numerify('STU-####'),
            'is_visible' => true,
        ];
    }

    private function randomCohortSubject(): Subject
    {
        return Subject::query()
            ->whereNotNull('faculty_code')
            ->whereNotNull('department_code')
            ->whereNotNull('level_code')
            ->where('is_visible', true)
            ->whereHas('faculty')
            ->whereHas('department')
            ->whereHas('level')
            ->inRandomOrder()
            ->firstOrFail();
    }

    private function randomCode(string $groupSlug): int
    {
        return (int) LtColumn::query()
            ->where('group_slug', $groupSlug)
            ->where('is_visible', true)
            ->inRandomOrder()
            ->value('code');
    }
}
