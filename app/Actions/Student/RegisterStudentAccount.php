<?php

namespace App\Actions\Student;

use App\Models\Student;
use App\Models\StudentAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * ينشئ حساب البوابة للطالب. الاسم ورقم الهوية يُؤخذان من السجل الأكاديمي لا من المتصفح،
 * ثم تُحدَّث بيانات الطالب الأساسية وصورته عبر الأفعال الموحّدة، وتُحذف المرفقات إن فشل الإنشاء.
 */
class RegisterStudentAccount
{
    /** المرفقات الاختيارية: الرقم الوطني، إثبات الشخصية، الشهادة السودانية */
    public const DOCUMENT_FIELDS = StoreStudentFiles::DOCUMENT_FIELDS;

    public function __construct(
        private readonly SyncStudentRecord $syncStudentRecord,
        private readonly StoreStudentFiles $storeStudentFiles,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile>  $documents
     */
    public function execute(Student $student, array $data, UploadedFile $photo, array $documents = []): StudentAccount
    {
        $documentPaths = $this->storeStudentFiles->storeDocuments($documents);

        try {
            return DB::transaction(function () use ($student, $data, $photo, $documentPaths): StudentAccount {
                $account = new StudentAccount;
                $account->forceFill([
                    ...collect($data)->except(['password_confirmation', 'photo', 'nationaltyno'])->all(),
                    'name' => $student->name_ar,
                    'name_en' => $student->name_en ?? ($data['name_en'] ?? null),
                    'university_no' => $student->university_no,
                    'nationaltyno' => $student->nationaltyno,
                    'files' => $documentPaths ?: null,
                ])->save();

                $this->syncStudentRecord->execute($student, $data);
                $this->storeStudentFiles->photo($student, $photo);

                return $account->refresh();
            });
        } catch (Throwable $exception) {
            Storage::disk(config('student.photo_disk'))->delete(array_values($documentPaths));

            throw $exception;
        }
    }
}
