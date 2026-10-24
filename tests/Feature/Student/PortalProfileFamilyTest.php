<?php

use App\Models\Student;
use App\Models\StudentAccount;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);

    $this->student = Student::factory()->create(['parent' => 'قديم', 'mother' => 'قديمة']);
    $this->account = StudentAccount::query()->forceCreate([
        'name' => $this->student->name_ar, 'email' => 'family@example.test', 'password' => 'x', 'university_no' => $this->student->university_no,
    ]);
});

it('shows the family data from the main student record on the profile', function () {
    $this->actingAs($this->account, 'student')
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('بيانات الأسرة')
        ->assertSee('قديم');
});

it('updates the main student record when the family data is edited', function () {
    $this->actingAs($this->account, 'student')
        ->patch(route('student.profile.update'), [
            'email' => 'family@example.test', 'mobile' => '0911000000',
            'parent' => 'الأب الجديد', 'parent_alive' => '1', 'parent_job' => 'معلم', 'parent_contacts' => '0922000000',
            'mother' => 'الأم الجديدة', 'mother_alive' => '', 'brothers' => '4',
            'name' => 'محاولة تغيير الاسم',
        ])
        ->assertRedirect(route('student.profile.edit'));

    $student = $this->student->fresh();

    expect($student->parent)->toBe('الأب الجديد')
        ->and($student->parent_alive)->toBeTrue()
        ->and($student->parent_job)->toBe('معلم')
        ->and($student->mother)->toBe('الأم الجديدة')
        ->and($student->mother_alive)->toBeNull()
        ->and($student->brothers)->toBe('4')
        ->and($student->mobile)->toBe('0911000000')
        ->and($student->name_ar)->not->toBe('محاولة تغيير الاسم');
});

it('keeps one unified photo for the table, the card and the portal when the profile photo changes', function () {
    $this->actingAs($this->account, 'student')
        ->patch(route('student.profile.update'), [
            'email' => 'family@example.test',
            'photo' => UploadedFile::fake()->image('new.png', 200, 200),
        ])
        ->assertSessionHasNoErrors();

    $student = $this->student->fresh();
    $path = 'students/avatar/std'.$student->university_no.'.png';

    expect($student->photo)->toBe($path)
        ->and($this->account->fresh()->avatar_url)->toBe($path)
        ->and($student->photoDataUri())->toStartWith('data:image/png;base64,')
        ->and($student->getFirstMedia('avatar'))->not->toBeNull();

    Storage::disk('public')->assertExists($path);

    $this->actingAs($this->account->fresh(), 'student')->get(route('student.photo'))->assertOk();
});

it('lets the student replace an attachment and view only their own files', function () {
    $this->actingAs($this->account, 'student')
        ->patch(route('student.profile.update'), [
            'email' => 'family@example.test',
            'identity_file' => UploadedFile::fake()->create('id.pdf', 50, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors();

    $path = $this->account->fresh()->files['identity_file'];
    Storage::disk('local')->assertExists($path);

    $this->actingAs($this->account->fresh(), 'student')->get(route('student.documents.show', 'identity_file'))->assertOk();
    $this->actingAs($this->account->fresh(), 'student')->get(route('student.documents.show', 'certificate_file'))->assertNotFound();
    auth('student')->logout();
    $this->get(route('student.documents.show', 'identity_file'))->assertRedirect(route('student.login'));
});

it('falls back to a default avatar when the student has no photo', function () {
    expect($this->student->photoDataUri())->toStartWith('data:image/svg+xml;base64,');
});

it('shows and migrates a legacy private photo into the unified student photo', function () {
    Storage::disk('local')->put('students/avatar/legacy.jpg', UploadedFile::fake()->image('legacy.jpg')->getContent());
    $this->account->forceFill(['avatar_url' => 'students/avatar/legacy.jpg'])->save();
    $student = $this->student->fresh();

    expect($student->photoUrl())->toContain('/photo?v=')
        ->and($student->photoDataUri())->toStartWith('data:image/');

    $this->artisan('students:sync-photos')->assertSuccessful();

    $student = $student->fresh();

    expect($student->photo)->toBe('students/avatar/std'.$student->university_no.'.jpg')
        ->and($student->photoContents())->not->toBeNull();
    Storage::disk('public')->assertExists($student->photo);
});

it('changes the photo url when the same-named photo file is replaced', function () {
    $path = 'students/avatar/std'.$this->student->university_no.'.png';
    Storage::disk('public')->put($path, UploadedFile::fake()->image('a.png', 20, 20)->getContent());
    $this->student->forceFill(['photo' => $path])->save();
    $before = $this->student->fresh()->photoUrl();

    Storage::disk('public')->put($path, UploadedFile::fake()->image('b.png', 300, 300)->getContent());

    expect($this->student->fresh()->photoUrl())->not->toBe($before);
});
