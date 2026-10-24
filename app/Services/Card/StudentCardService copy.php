<?php

namespace App\Services\Card;

use App\Models\Reg\Card;
use App\Traits\ArabicGlyph;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentCardService
{
    use ArabicGlyph;

    public static function generate(Card $card): string
    {

        $card->loadMissing([
            'student.faculty',
            'student.department',
            'student.level',
        ]);

        $student = $card->student;

        if (! $student) {
            throw new RuntimeException(
                'لا يوجد طالب مرتبط بهذه البطاقة.'
            );
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
                public_path('images/kassala.png')
            ),

            'photo' => self::mediaToBase64(
                $student,
                'avatar'
            ),

            'title' => self::ar('جامعة كسلا'),
            'in_card' => self::ar('بطاقة هوية طالب'),
            'content' => self::ar('أمانة الشؤون العلمية'),

            'full_name' => self::ar('الاسم الكامل'),
            'stu_num' => self::ar('الرقم الجامعي'),
            'std_prog' => self::ar('الكلية'),
            'std_level' => self::ar('المستوى'),
            'std_status' => self::ar('الحالة'),
            'active_status' => self::ar('نشط'),
            'std_card_expi' => self::ar('صالحة حتى'),
            'std_card' => self::ar('رقم البطاقة'),

            'in_come' => self::ar('يجب إبراز هذه البطاقة'),
            'in_come_card' => self::ar(
                'عند الدخول للحرم الجامعي'
            ),

            'name_ar' => self::ar(
                $student->name_ar
            ),

            'faculty' => self::ar(
                $student->faculty?->name_ar ?? ''
            ),

            'department' => self::ar(
                $student->department?->name_ar ?? ''
            ),

            'level' => self::ar(
                $student->level?->name_ar ?? ''
            ),

            'university_no' => (string) $student->university_no,

            'card_number' => (string) $card->card_number,

            'expiry_date' => $card->expiry_date
                ? Carbon::parse($card->expiry_date)
                    ->format('Y-m-d')
                : '',

            'mag_track_1' => '',
            'mag_track_2' => '',
        ];

        $pdf = Pdf::setOptions([
            'isFontSubsettingEnabled' => true,
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Cairo',
            'dpi' => 300,
        ])->loadView(
            'cards.card_template',
            compact('card', 'qrCode', 'data')
        );

        $pdf->setPaper([
            0,
            0,
            242.65,
            153.07,
        ]);

        $directory = storage_path('app/cards');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $file = $directory
            .DIRECTORY_SEPARATOR
            .'student-card-'
            .$card->getKey()
            .'.pdf';

        file_put_contents(
            $file,
            $pdf->output()
        );

        return $file;
    }

    private static function mediaToBase64(
        $model,
        string $collection
    ): ?string {
        if (! $model->hasMedia($collection)) {
            return null;
        }

        $path = $model->getFirstMediaPath($collection);

        return self::imageToBase64($path);
    }

    private static function imageToBase64(
        string $path
    ): ?string {
        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($path)
            ?: 'application/octet-stream';

        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($contents)
        );
    }
}
