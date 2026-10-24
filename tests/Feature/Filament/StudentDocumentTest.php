<?php

use App\Models\Student;
use App\Models\StudentAccount;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
    Storage::fake('local');

    $this->student = Student::factory()->create();
    Storage::disk('local')->put('students/documents/id.pdf', '%PDF-1.4 test');
    Storage::disk('local')->put('students/avatar/photo.jpg', 'jpeg-bytes');
    StudentAccount::query()->forceCreate([
        'name' => 'طالب', 'email' => 'doc@example.test', 'password' => 'x', 'university_no' => $this->student->university_no,
        'avatar_url' => 'students/avatar/photo.jpg', 'files' => ['identity_file' => 'students/documents/id.pdf'],
    ]);
});

it('shows a registered attachment to staff who may view students', function () {
    actingAsSuperAdmin();

    $this->get(route('students.documents.show', [$this->student, 'identity_file']))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->get(route('students.documents.download', [$this->student, 'identity_file']))
        ->assertOk()
        ->assertHeader('content-disposition');

    $this->get(route('students.documents.show', [$this->student, 'photo']))->assertOk();
});

it('returns 404 for unknown or missing attachments', function () {
    actingAsSuperAdmin();

    $this->get(route('students.documents.show', [$this->student, 'certificate_file']))->assertNotFound();
    $this->get(route('students.documents.show', [$this->student, '../secret']))->assertNotFound();
});

it('keeps attachments away from guests', function () {
    $this->get(route('students.documents.show', [$this->student, 'identity_file']))->assertRedirect();
});

it('renders the attachments viewer on the student view page', function () {
    actingAsSuperAdmin();

    Livewire\Livewire::test(App\Filament\Clusters\Reg\Resources\Students\Pages\ViewStudents::class, ['record' => $this->student->getKey()])
        ->assertSuccessful()
        ->assertSee('المرفقات')
        ->assertSee('إثبات الشخصية');
});

it('serves the unified student photo to staff without relying on the public storage link', function () {
    actingAsSuperAdmin();
    Storage::fake('public');
    Storage::disk('public')->put('students/avatar/std1.jpg', Illuminate\Http\UploadedFile::fake()->image('p.jpg')->getContent());
    $this->student->forceFill(['photo' => 'students/avatar/std1.jpg'])->save();

    $this->get($this->student->fresh()->photoUrl())->assertOk()->assertHeader('Content-Type', 'image/jpeg');
});

it('keeps the student photo away from guests', function () {
    $this->get(route('students.photo.show', $this->student))->assertRedirect();
});
