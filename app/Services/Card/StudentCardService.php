<?php

namespace App\Services\Card;

use App\Models\Reg\Card;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentCardService
{
    public static function generate(Card $card): string
    {
        $card->loadMissing([
            'student.faculty',
            'student.department',
            'student.level',
        ]);

        $student = $card->student;

        if (! $student) {
            throw new RuntimeException('لا يوجد طالب مرتبط بهذه البطاقة.');
        }

        $json = json_encode([
            'id' => $student->university_no,
            'name' => $student->name_ar,
            'faculty' => $student->faculty?->name_ar,
            'expiry' => $card->expiry_date,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $qrCode = base64_encode(
            QrCode::format('svg')
                ->encoding('UTF-8')
                ->size(180)
                ->margin(0)
                ->errorCorrection('H')
                ->generate($json)
        );

        $data = [
            'logo' => self::imageToBase64(
                public_path(config('university.logo'))
            ),

            'avatar' => self::studentPhotoToBase64($student),
            'photo' => self::studentPhotoToBase64($student),

            'title' => config('university.name_ar'),
            'in_card' => 'بطاقة  طالب',
            'content' => 'أمانة الشؤون العلمية',

            'full_name_label' => 'الاسم الكامل',
            'stu_num_label' => 'الرقم الجامعي',
            'std_prog_label' => 'الكلية',
            'std_level_label' => 'الفصل',
            'std_status_label' => 'الحالة',
            'active_status' => 'نشط',
            'std_card_expi_label' => 'صالحة حتى',
            'std_card_label' => 'رقم البطاقة',

            'in_come' => 'يجب إبراز هذه البطاقة',
            'in_come_card' => 'عند الدخول للحرم الجامعي',

            // البيانات الفعلية للطالب
            'name_ar' => $student->name_ar,
            'faculty' => $student->faculty?->name_ar ?? '',
            'department' => $student->department?->name_ar ?? '',
            'level' => $student->level?->name_ar ?? '',
            'university_no' => (string) $student->university_no,
            'card_number' => (string) $card->card_number,
            'expiry_date' => $card->expiry_date ? Carbon::parse($card->expiry_date)->format('Y-m-d') : '',
        ];

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // 2. تهيئة mPDF مع إعدادات البطاقة واللغة العربية
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [85.6, 53.98], // مقاس بطاقة CR80
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'), // مسار مجلد الخطوط الخاص بك
            ]),
            'fontdata' => $fontData + [
                'cairo' => [
                    'R' => 'Cairo-Regular.ttf', // تأكد من تطابق اسم الملف
                    'B' => 'Cairo-Bold.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'cairo',
        ]);

        // تفعيل دعم اللغة العربية والاتجاه من اليمين لليسار
        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        // تجهيز وعرض القالب
        $html = view('cards.card_template', compact('card', 'qrCode', 'data'))->render();

        $mpdf->WriteHTML($html);

        // حفظ الملف
        $directory = storage_path('app/cards');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $file = $directory.DIRECTORY_SEPARATOR.'student-card-'.$card->getKey().'.pdf';

        // Output كملف
        $mpdf->Output($file, Destination::FILE);

        return $file;
    }

    private static function mediaToBase64($model, string $collection): ?string
    {
        if (! $model->hasMedia($collection)) {
            return null;
        }

        return self::imageToBase64($model->getFirstMediaPath($collection));
    }

    private static function imageToBase64(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }
        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }
        $mimeType = mime_content_type($path) ?: 'application/octet-stream';

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($contents));
    }

    private static function studentPhotoToBase64(Student $student): string
    {
        return $student->photoDataUri();
    }

    private static function detectImageMimeType(string $contents): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->buffer($contents);

        return str_starts_with($mime, 'image/')
            ? $mime
            : null;
    }
}
