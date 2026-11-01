<?php

namespace App\Console\Commands;

use App\Actions\Student\StoreStudentFiles;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

/**
 * يستورد صور الطلاب من مشروع kassala القديم (ملفات std{الرقم الجامعي}.jpg) إلى الصورة الموحّدة في سجل الطالب.
 */
class ImportLegacyStudentPhotos extends Command
{
    protected $signature = 'students:import-legacy-photos
        {path : مجلد الصور القديم، مثل /var/www/reg/kassala/public/storage/students/avatar}
        {--overwrite : استبدال صورة الطالب إن كانت له صورة}
        {--dry-run : عرض ما سيُستورد دون تنفيذ}';

    protected $description = 'Import student photos uploaded in the legacy kassala project (std{university_no}.ext)';

    public function handle(StoreStudentFiles $storeStudentFiles): int
    {
        $path = (string) $this->argument('path');

        if (! is_dir($path)) {
            $this->error("المجلد غير موجود: {$path}");

            return self::FAILURE;
        }

        $counts = ['imported' => 0, 'has_photo' => 0, 'no_student' => 0, 'invalid' => 0];

        foreach (glob(rtrim($path, '/\\').DIRECTORY_SEPARATOR.'std*') ?: [] as $file) {
            $name = basename($file);

            if (preg_match('/^std(\d+)\.(jpe?g|png)$/i', $name, $match) !== 1 || @getimagesize($file) === false) {
                $counts['invalid']++;
                $this->line("تجاهل (اسم أو صورة غير صالحة): {$name}");

                continue;
            }

            $student = Student::query()->find($match[1]);

            if (! $student) {
                $counts['no_student']++;

                continue;
            }

            if (filled($student->photo) && ! $this->option('overwrite')) {
                $counts['has_photo']++;

                continue;
            }

            if (! $this->option('dry-run')) {
                $storeStudentFiles->photo($student, new UploadedFile($file, $name, null, null, true));
            }

            $counts['imported']++;
        }

        $this->info(($this->option('dry-run') ? 'سيُستورد: ' : 'تم استيراد: ').$counts['imported']);
        $this->line("لهم صورة سابقة (تُركت): {$counts['has_photo']} — بلا طالب مطابق: {$counts['no_student']} — ملفات غير صالحة: {$counts['invalid']}");

        return self::SUCCESS;
    }
}
