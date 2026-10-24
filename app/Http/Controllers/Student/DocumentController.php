<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\StoreStudentFiles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * يعرض لصاحب الحساب فقط المرفقات التي رفعها (قرص خاص).
 */
class DocumentController extends Controller
{
    public function show(Request $request, string $document): StreamedResponse
    {
        abort_unless(in_array($document, StoreStudentFiles::DOCUMENT_FIELDS, true), 404);

        $path = $request->user('student')->files[$document] ?? null;
        $disk = Storage::disk(config('student.photo_disk'));

        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
