<?php

use App\Actions\Student\VerifyStudentIdentity;
use App\Models\Student;
use App\Models\StudentAccount;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
    RateLimiter::clear('student-register');
    Storage::fake('local');
    Storage::fake('public');
    $this->student = Student::factory()->create(['nationaltyno' => '1234567890', 'name_ar' => 'محمد أحمد علي', 'name_en' => 'Mohamed Ahmed Ali']);
});

/**
 * @return array<string, mixed>
 */
function registrationPayload(Student $student, array $overrides = []): array
{
    return array_merge([
        'university_no' => (string) $student->university_no,
        'nationaltyno' => '1234567890',
        'email' => 'new.student@example.test',
        'password' => 'Passw0rd!x',
        'password_confirmation' => 'Passw0rd!x',
        'photo' => UploadedFile::fake()->image('photo.jpg'),
        'state_code' => 1,
        'city' => 'بورتسودان',
        'localty' => 'بورتسودان',
        'block' => 'الحي الأول',
        'home_no' => '12',
        'mobile' => '0912345678',
        'parent' => 'ولي الأمر',
        'parent_alive' => 1,
        'parent_job' => 'موظف',
        'parent_address' => 'بورتسودان',
        'parent_contacts' => '0911111111',
        'mother' => 'الأم',
        'mother_alive' => 1,
    ], $overrides);
}

it('verifies the university number only together with the matching national id', function () {
    $identity = app(VerifyStudentIdentity::class);

    expect($identity->execute((string) $this->student->university_no, '1234567890'))->not->toBeNull()
        ->and($identity->execute((string) $this->student->university_no, '1234 567 890'))->not->toBeNull()
        ->and($identity->execute((string) $this->student->university_no, '0000000000'))->toBeNull()
        ->and($identity->execute('99999999', '1234567890'))->toBeNull();
});

it('verifies by university number alone when the national id is left empty', function () {
    $student = Student::factory()->create(['nationaltyno' => null]);

    expect(app(VerifyStudentIdentity::class)->execute((string) $student->university_no, ''))->not->toBeNull()
        ->and(app(VerifyStudentIdentity::class)->execute((string) $student->university_no, '123'))->toBeNull();
});

it('answers the verification step with the same generic failure for every kind of mismatch', function () {
    $taken = Student::factory()->create(['nationaltyno' => '5555555555']);
    StudentAccount::query()->forceCreate([
        'name' => $taken->name_ar, 'email' => 'taken@example.test', 'password' => 'x', 'university_no' => $taken->university_no,
    ]);

    $responses = collect([
        ['university_no' => '99999999', 'nationaltyno' => '1234567890'],
        ['university_no' => (string) $this->student->university_no, 'nationaltyno' => '0000000000'],
        ['university_no' => (string) $taken->university_no, 'nationaltyno' => '5555555555'],
    ])->map(fn (array $payload) => $this->postJson(route('student.register.verify'), $payload));

    $responses->each(fn ($response) => $response->assertStatus(422)->assertExactJson([
        'valid' => false,
        'message' => __('student_portal.identity_mismatch'),
    ]));
});

it('confirms a matching identity and returns the student name and faculty', function () {
    $this->postJson(route('student.register.verify'), [
        'university_no' => (string) $this->student->university_no,
        'nationaltyno' => '1234567890',
    ])->assertOk()->assertExactJson([
        'valid' => true,
        'name' => 'محمد أحمد علي',
        'name_en' => 'Mohamed Ahmed Ali',
        'university_no' => $this->student->university_no,
        'faculty' => $this->student->faculty->name_ar,
        'department' => $this->student->department?->name_ar,
        'level' => $this->student->level?->name_ar,
    ]);
});

it('registers a student, takes the name from the academic record and keeps one unified photo', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, ['name' => 'اسم مزوّر']))
        ->assertRedirect(route('student.dashboard'));

    $account = StudentAccount::query()->where('university_no', $this->student->university_no)->firstOrFail();

    expect($account->name)->toBe('محمد أحمد علي')
        ->and($account->name_en)->toBe('Mohamed Ahmed Ali')
        ->and($account->nationaltyno)->toBe('1234567890')
        ->and($account->avatar_url)->toBe($this->student->fresh()->photo);

    Storage::disk('public')->assertExists($account->avatar_url);
    $this->assertAuthenticatedAs($account, 'student');
});

it('rejects a registration whose national id does not match the academic record', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, ['nationaltyno' => '0000000000']))
        ->assertSessionHasErrors(['university_no' => __('student_portal.identity_mismatch')]);

    expect(StudentAccount::query()->count())->toBe(0);
    $this->assertGuest('student');
});

it('does not allow a second account for the same student', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student))->assertRedirect();
    auth('student')->logout();

    $this->post(route('student.register.store'), registrationPayload($this->student, ['email' => 'second@example.test']))
        ->assertSessionHasErrors('university_no');

    expect(StudentAccount::query()->where('university_no', $this->student->university_no)->count())->toBe(1);
});

it('requires a profile photo and a strong password', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, [
        'photo' => null, 'password' => 'short', 'password_confirmation' => 'short',
    ]))->assertSessionHasErrors(['photo', 'password']);

    expect(StudentAccount::query()->count())->toBe(0);
});

it('throttles registration attempts per university number', function () {
    config(['student.rate_limits.verify_per_university_no_per_hour' => 5]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('student.register.verify'), ['university_no' => '777', 'nationaltyno' => 'x'])->assertStatus(422);
    }

    $this->postJson(route('student.register.verify'), ['university_no' => '777', 'nationaltyno' => 'x'])->assertStatus(429);
});

it('enforces one portal account per university number at the database level', function () {
    StudentAccount::query()->forceCreate(['name' => 'a', 'email' => 'a@example.test', 'password' => 'x', 'university_no' => $this->student->university_no]);

    expect(fn () => StudentAccount::query()->forceCreate(['name' => 'b', 'email' => 'b@example.test', 'password' => 'x', 'university_no' => $this->student->university_no]))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

it('registers a student without entering the national id', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, ['nationaltyno' => '']))
        ->assertRedirect(route('student.dashboard'));

    expect(StudentAccount::query()->where('university_no', $this->student->university_no)->value('nationaltyno'))->toBe('1234567890');
});

it('treats the mother alive field as optional', function () {
    $payload = registrationPayload($this->student);
    unset($payload['mother_alive']);

    $this->post(route('student.register.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('student.dashboard'));

    expect(StudentAccount::query()->where('university_no', $this->student->university_no)->value('mother_alive'))->toBeNull();
});

it('does not throttle the verification step when the throttle flag is off', function () {
    config(['student.rate_limits.verify_throttle_enabled' => false, 'student.rate_limits.verify_per_university_no_per_hour' => 1]);

    foreach (range(1, 4) as $attempt) {
        $this->postJson(route('student.register.verify'), ['university_no' => '777'])->assertStatus(422);
    }
});

it('stores the optional attachments privately and keeps them optional', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, [
        'national_id_file' => UploadedFile::fake()->create('national.pdf', 100, 'application/pdf'),
        'identity_file' => UploadedFile::fake()->image('identity.jpg'),
        'certificate_file' => UploadedFile::fake()->image('certificate.png'),
    ]))->assertRedirect(route('student.dashboard'));

    $account = StudentAccount::query()->where('university_no', $this->student->university_no)->firstOrFail();

    expect($account->files)->toHaveKeys(['national_id_file', 'identity_file', 'certificate_file']);
    foreach ($account->files as $path) {
        Storage::disk('local')->assertExists($path);
    }
});

it('rejects attachments of the wrong type', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, [
        'identity_file' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
    ]))->assertSessionHasErrors('identity_file');

    expect(StudentAccount::query()->count())->toBe(0);
});

it('updates the main student record and photo when the student registers', function () {
    $this->post(route('student.register.store'), registrationPayload($this->student, [
        'mobile' => '0911222333', 'parent' => 'ولي الأمر الجديد', 'mother' => 'الأم الجديدة', 'mother_alive' => 1, 'city' => 'كسلا',
    ]))->assertRedirect(route('student.dashboard'));

    $student = $this->student->fresh();

    expect($student->mobile)->toBe('0911222333')
        ->and($student->parent)->toBe('ولي الأمر الجديد')
        ->and($student->mother)->toBe('الأم الجديدة')
        ->and($student->mother_alive)->toBeTrue()
        ->and($student->city)->toBe('كسلا')
        ->and($student->photo)->toBe('students/avatar/std'.$student->university_no.'.jpg')
        ->and($student->getFirstMedia('avatar'))->not->toBeNull();

    Storage::disk('public')->assertExists($student->photo);
});
