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
            'student.batch',
        ]);

        $student = $card->student;

        if (! $student) {
            throw new RuntimeException('لا يوجد طالب مرتبط بهذه البطاقة.');
        }

        $qrCode = base64_encode(
            QrCode::format('svg')
                ->size(180)
                ->margin(0)
                ->errorCorrection('M')
                ->generate(CardVerificationService::url($card))
        );

        $data = [
            'logo' => self::imageToBase64(
                public_path(config('university.logo'))
            ),

            'avatar' => self::studentPhotoToBase64($student),
            'photo' => self::studentPhotoToBase64($student),

            'title' => config('university.name_ar'),
            'in_card' => config('card.badge_ar'),
            'badge_en' => config('card.badge_en'),
            'content' => config('card.issuer_ar'),
            'issuer_en' => config('card.issuer_en'),
            'website' => config('card.website'),

            'full_name_label' => config('card.labels.full_name'),
            'stu_num_label' => config('card.labels.university_no'),
            'std_prog_label' => config('card.labels.faculty'),
            'std_level_label' => config('card.labels.level'),
            'batch_label' => config('card.labels.batch'),
            'std_status_label' => config('card.labels.status'),
            'active_status' => config('card.labels.active'),
            'std_card_expi_label' => config('card.labels.expiry'),
            'std_card_label' => config('card.labels.card_number'),
            'signature_label' => config('card.labels.signature'),

            'in_come' => config('card.instructions.show_card'),
            'in_come_card' => config('card.instructions.show_card_at'),
            'scan_note' => config('card.instructions.scan'),
            'return_note' => config('card.instructions.return'),

            // البيانات الفعلية للطالب
            'name_ar' => $student->name_ar,
            'faculty' => $student->faculty?->name_ar ?? '',
            'department' => $student->department?->name_ar ?? '',
            'level' => $student->level?->name_ar ?? '',
            'batch' => $student->batch?->name_ar ?? '',
            'university_no' => (string) $student->university_no,
            'card_number' => (string) $card->card_number,
            'expiry_date' => $card->expiry_date ? Carbon::parse($card->expiry_date)->format('Y-m-d') : '',
        ] + self::palette($student->faculty?->primary_hex);

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // 2. تهيئة mPDF مع إعدادات البطاقة واللغة العربية
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'tempDir' => storage_path('app/mpdf'),
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

    /**
     * لون البطاقة من لون الكلية إن وُجد وصحّ، وإلا الأخضر الافتراضي، مع درجة أغمق للتدرّج.
     *
     * @return array{primary: string, primary_dark: string, primary_tint: string}
     */
    public static function palette(?string $hex): array
    {
        $hex = trim((string) $hex);

        if (preg_match('/^#?([0-9a-f]{6})$/i', $hex, $match) !== 1) {
            $hex = config('card.default_color');
            preg_match('/^#?([0-9a-f]{6})$/i', $hex, $match);
        }

        $rgb = array_map('hexdec', str_split($match[1], 2));
        $dark = array_map(fn (int $channel): int => (int) round($channel * 0.55), $rgb);

        $tint = array_map(fn (int $channel): int => (int) round($channel + (255 - $channel) * 0.9), $rgb);

        return [
            'primary_tint' => sprintf('#%02X%02X%02X', ...$tint),
            'primary' => '#'.strtoupper($match[1]),
            'primary_dark' => sprintf('#%02X%02X%02X', ...$dark),
        ];
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
