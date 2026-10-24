<?php

namespace App\Actions\Student;

use App\Models\Student;
use App\Models\StudentAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * المكان الوحيد لحفظ صورة الطالب ومرفقاته (التسجيل والملف الشخصي).
 * الصورة: نسخة واحدة على القرص العام تستعملها لوحة الإدارة والبطاقة وطباعة FARGO وبوابة الطالب.
 * المرفقات: على القرص الخاص باسم عشوائي، ومساراتها في حقل files بحساب الطالب.
 */
class StoreStudentFiles
{
    /** المرفقات الاختيارية: الرقم الوطني، إثبات الشخصية، الشهادة السودانية */
    public const DOCUMENT_FIELDS = ['national_id_file', 'identity_file', 'certificate_file'];

    public function photo(Student $student, UploadedFile $photo): string
    {
        $disk = Storage::disk('public');
        $extension = strtolower($photo->getClientOriginalExtension() ?: $photo->extension());
        $path = $disk->putFileAs('students/avatar', $photo, 'std'.$student->university_no.'.'.$extension);

        if (filled($student->photo) && $student->photo !== $path) {
            $disk->delete($student->photo);
        }

        $student->forceFill(['photo' => $path])->save();
        $student->clearMediaCollection('avatar');
        $student->addMedia($photo)->preservingOriginal()->toMediaCollection('avatar');
        $student->portalUser?->forceFill(['avatar_url' => $path])->save();

        return $path;
    }

    /**
     * يحفظ ملفات المرفقات ويرجع مساراتها بحسب الحقل.
     *
     * @param  array<string, UploadedFile>  $documents
     * @return array<string, string>
     */
    public function storeDocuments(array $documents): array
    {
        $disk = Storage::disk(config('student.photo_disk'));

        return collect($documents)
            ->only(self::DOCUMENT_FIELDS)
            ->map(fn (UploadedFile $file): string => (string) $disk->putFile(config('student.documents_directory'), $file))
            ->all();
    }

    /**
     * يستبدل مرفقات حساب قائم ويحذف الملفات القديمة المستبدلة.
     *
     * @param  array<string, UploadedFile>  $documents
     */
    public function replaceDocuments(StudentAccount $account, array $documents): void
    {
        if ($documents === []) {
            return;
        }

        $files = $account->files ?? [];
        $stored = $this->storeDocuments($documents);
        $disk = Storage::disk(config('student.photo_disk'));

        foreach ($stored as $key => $path) {
            if (filled($files[$key] ?? null)) {
                $disk->delete($files[$key]);
            }

            $files[$key] = $path;
        }

        $account->forceFill(['files' => $files])->save();
    }
}
