<?php

use App\Models\Student;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    migrateErpSchema();
    Storage::fake('public');

    $this->folder = sys_get_temp_dir().'/legacy-photos-'.uniqid();
    mkdir($this->folder);

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');

    foreach (['std101.png', 'std102.png', 'std999.png', 'std_temp_x.png'] as $name) {
        file_put_contents("{$this->folder}/{$name}", $png);
    }

    file_put_contents("{$this->folder}/std103.png", 'not an image');

    foreach ([101, 102, 103] as $number) {
        Student::query()->forceCreate(['university_no' => $number, 'name_ar' => "طالب {$number}", 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026]);
    }
});

afterEach(fn () => array_map('unlink', glob("{$this->folder}/*")) && rmdir($this->folder));

it('imports legacy photos for existing students and skips unusable files', function () {
    $this->artisan('students:import-legacy-photos', ['path' => $this->folder])->assertSuccessful();

    expect(Student::query()->find(101)->photo)->toBe('students/avatar/std101.png')
        ->and(Student::query()->find(102)->photo)->toBe('students/avatar/std102.png')
        ->and(Student::query()->find(103)->photo)->toBeNull();

    Storage::disk('public')->assertExists('students/avatar/std101.png');
});

it('keeps an existing photo unless overwrite is requested and changes nothing on dry run', function () {
    Storage::disk('public')->put('students/avatar/old.jpg', 'x');
    Student::query()->find(101)->forceFill(['photo' => 'students/avatar/old.jpg'])->save();

    $this->artisan('students:import-legacy-photos', ['path' => $this->folder, '--dry-run' => true])->assertSuccessful();
    expect(Student::query()->find(102)->photo)->toBeNull();

    $this->artisan('students:import-legacy-photos', ['path' => $this->folder])->assertSuccessful();
    expect(Student::query()->find(101)->photo)->toBe('students/avatar/old.jpg');

    $this->artisan('students:import-legacy-photos', ['path' => $this->folder, '--overwrite' => true])->assertSuccessful();
    expect(Student::query()->find(101)->photo)->toBe('students/avatar/std101.png');
});

it('treats a junk photo value whose file does not exist as no photo', function () {
    Student::query()->find(101)->forceFill(['photo' => '0'])->save();

    $this->artisan('students:import-legacy-photos', ['path' => $this->folder])->assertSuccessful();

    expect(Student::query()->find(101)->photo)->toBe('students/avatar/std101.png');
});
