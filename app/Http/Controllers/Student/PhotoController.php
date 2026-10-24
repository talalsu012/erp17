<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * يعرض الصورة الشخصية لصاحب الحساب من النسخة الموحّدة في سجل الطالب (مع الرجوع لنسخة الحساب القديمة).
 */
class PhotoController extends Controller
{
    public function show(Request $request): Response
    {
        $student = $request->user('student')->student;
        $contents = $student?->photoContents();

        if ($contents === null) {
            $legacy = $request->user('student')->avatar_url;
            $disk = Storage::disk(config('student.photo_disk'));

            abort_unless($legacy && $disk->exists($legacy), 404);

            return $disk->response($legacy);
        }

        return response($contents, 200, [
            'Content-Type' => (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: 'image/jpeg',
            'Cache-Control' => 'private, no-cache',
        ]);
    }
}
