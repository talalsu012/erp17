<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * يعرض لموظفي الإدارة المرفقات الخاصة التي رفعها الطالب عند التسجيل (قرص خاص، بصلاحية عرض الطالب).
 */
class StudentDocumentController extends Controller
{
    public function show(Student $student, string $document, bool $download = false): StreamedResponse
    {
        Gate::authorize('view', $student);

        abort_unless(in_array($document, ['photo', ...array_keys(self::labels())], true), 404);

        $account = $student->portalUser;
        $path = $document === 'photo' ? $account?->avatar_url : ($account?->files[$document] ?? null);
        $disk = Storage::disk(config('student.photo_disk'));

        abort_if(blank($path) || ! $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], $download ? 'attachment' : 'inline');
    }

    /**
     * الصورة الموحّدة للطالب (العامة أو القديمة) لأي موظف يملك صلاحية العرض.
     */
    public function photo(Student $student): Response
    {
        Gate::authorize('view', $student);

        $contents = $student->photoContents();

        abort_if($contents === null, 404);

        return response($contents, 200, [
            'Content-Type' => (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: 'image/jpeg',
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Student $student, string $document): StreamedResponse
    {
        return $this->show($student, $document, true);
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'national_id_file' => 'الرقم الوطني',
            'identity_file' => 'إثبات الشخصية',
            'certificate_file' => 'الشهادة السودانية',
        ];
    }
}
