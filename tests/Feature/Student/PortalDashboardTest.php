<?php

use App\Models\Student;
use App\Models\StudentAccount;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
    Storage::fake('local');
});

function portalAccountFor(Student $student, array $attributes = []): StudentAccount
{
    return StudentAccount::query()->forceCreate(array_merge([
        'name' => $student->name_ar,
        'email' => 'portal'.$student->university_no.'@example.test',
        'password' => Hash::make('Passw0rd!x'),
        'university_no' => $student->university_no,
    ], $attributes));
}

it('renders the dashboard with only the signed-in student data', function () {
    $mine = Student::factory()->create(['name_ar' => 'طالب أول فريد', 'student_account' => null]);
    $other = Student::factory()->create(['name_ar' => 'طالب ثان مختلف']);

    $response = $this->actingAs(portalAccountFor($mine), 'student')->get(route('student.dashboard'));

    $response->assertOk()
        ->assertSee('طالب أول فريد')
        ->assertDontSee('طالب ثان مختلف')
        ->assertViewHas('student', fn (Student $student) => $student->is($mine));
});

it('hides results until the student account is cleared financially', function () {
    $student = Student::factory()->create(['student_account' => null]);
    $account = portalAccountFor($student);

    $this->actingAs($account, 'student')->get(route('student.dashboard'))
        ->assertViewHas('canViewResults', false)
        ->assertViewHas('terms', fn ($terms) => $terms->isEmpty());

    $student->update(['student_account' => Student::CLEARED_ACCOUNT_CODE]);

    $this->actingAs($account, 'student')->get(route('student.dashboard'))
        ->assertViewHas('canViewResults', true);
});

it('never takes the student identifier from the request', function () {
    $mine = Student::factory()->create(['name_ar' => 'صاحب الحساب']);
    $other = Student::factory()->create(['name_ar' => 'طالب آخر']);

    $this->actingAs(portalAccountFor($mine), 'student')
        ->get(route('student.dashboard', ['university_no' => $other->university_no]))
        ->assertOk()
        ->assertSee('صاحب الحساب')
        ->assertDontSee('طالب آخر');
});

it('serves the profile photo only to its owner from the private disk', function () {
    $student = Student::factory()->create();
    Storage::disk('local')->put('students/avatar/mine.jpg', 'binary-photo');
    $account = portalAccountFor($student, ['avatar_url' => 'students/avatar/mine.jpg']);

    $this->get(route('student.photo'))->assertRedirect(route('student.login'));

    $this->actingAs($account, 'student')->get(route('student.photo'))->assertOk();
});

it('answers 404 when the student has no stored photo', function () {
    $account = portalAccountFor(Student::factory()->create());

    $this->actingAs($account, 'student')->get(route('student.photo'))->assertNotFound();
});

it('updates the contact data but never the name', function () {
    $student = Student::factory()->create(['name_ar' => 'اسم السجل']);
    $account = portalAccountFor($student);

    $this->actingAs($account, 'student')
        ->patch(route('student.profile.update'), ['email' => 'changed@example.test', 'mobile' => '0999999999', 'name' => 'اسم جديد'])
        ->assertRedirect(route('student.profile.edit'));

    $fresh = $account->fresh();

    expect($fresh->email)->toBe('changed@example.test')
        ->and($fresh->mobile)->toBe('0999999999')
        ->and($fresh->name)->toBe('اسم السجل');
});

it('refuses an email already used by another student account', function () {
    $first = portalAccountFor(Student::factory()->create(), ['email' => 'dup@example.test']);
    $second = portalAccountFor(Student::factory()->create());

    $this->actingAs($second, 'student')
        ->patch(route('student.profile.update'), ['email' => $first->email])
        ->assertSessionHasErrors('email');
});

it('changes the password only with the current password', function () {
    $account = portalAccountFor(Student::factory()->create());

    $this->actingAs($account, 'student')
        ->put(route('student.password.update'), ['current_password' => 'wrong', 'password' => 'N3wPassword!', 'password_confirmation' => 'N3wPassword!'])
        ->assertSessionHasErrors('current_password');

    $this->actingAs($account, 'student')
        ->put(route('student.password.update'), ['current_password' => 'Passw0rd!x', 'password' => 'N3wPassword!', 'password_confirmation' => 'N3wPassword!'])
        ->assertSessionHasNoErrors();

    expect(Hash::check('N3wPassword!', $account->fresh()->password))->toBeTrue();
});

it('has no self-service account deletion', function () {
    $account = portalAccountFor(Student::factory()->create());

    $this->actingAs($account, 'student')->delete('/student/profile')->assertStatus(405);

    expect(StudentAccount::query()->whereKey($account->getKey())->exists())->toBeTrue();
});

it('renders the documents section for a student whose card dates and status come back as plain values', function () {
    $student = Student::factory()->create();
    $account = portalAccountFor($student);
    App\Models\Reg\Card::query()->create([
        'university_no' => $student->university_no, 'card_number' => 'C-9', 'issue_date' => '2026-02-01', 'expiry_date' => '2027-02-01', 'status' => 'active',
    ]);

    $this->actingAs($account, 'student')
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('2026-02-01')
        ->assertSee('2027-02-01');
});
