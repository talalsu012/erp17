<?php

namespace App\Console\Commands;

use App\Actions\Student\StoreStudentFiles;
use App\Models\StudentAccount;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * ينقل الصور الخاصة القديمة لحسابات الطلاب إلى النسخة الموحّدة في سجل الطالب (جدول students).
 */
class SyncStudentPhotos extends Command
{
    protected $signature = 'students:sync-photos {--dry-run : عرض ما سيُنقل دون تنفيذ}';

    protected $description = 'Copy legacy private portal photos into the unified student photo';

    public function handle(StoreStudentFiles $storeStudentFiles): int
    {
        $disk = Storage::disk(config('student.photo_disk'));
        $moved = 0;

        StudentAccount::query()
            ->whereNotNull('avatar_url')
            ->with('student')
            ->each(function (StudentAccount $account) use ($disk, $storeStudentFiles, &$moved): void {
                $student = $account->student;

                if (! $student || filled($student->photo) || ! $disk->exists($account->avatar_url)) {
                    return;
                }

                $this->line("{$student->university_no}: {$account->avatar_url}");

                if (! $this->option('dry-run')) {
                    $storeStudentFiles->photo($student, new UploadedFile($disk->path($account->avatar_url), basename($account->avatar_url), null, null, true));
                }

                $moved++;
            });

        $this->info(($this->option('dry-run') ? 'سيُنقل: ' : 'تم نقل: ').$moved);

        return self::SUCCESS;
    }
}
