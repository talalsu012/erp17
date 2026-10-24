<?php

namespace App\Models\Reg;

use App\Models\Student;
use App\Reg\CardStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Card extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    public $titles = 'اكاديمية السودان للعلوم المصرفية والمالية';

    protected $table = 'reg_cards';

    protected $guarded = [];

    protected $casts = [
        'status' => CardStatus::class,
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            // 'status' => CardStatus::class,
        ];
    }

    public function generateCardNumber(): string
    {
        return sprintf(
            '%s-%s-%s',
            $this->university_no,
            now()->format('ym'),
            Str::upper(Str::random(4))
        );
    }

    public static function generateCardNumberOld(string $universityNo): string
    {
        $prefix = 'UNI'; // University prefix
        $universityPart = strtoupper(substr($universityNo, 0, 5)); // First 5 chars of university no
        $randomPart = Str::upper(Str::random(4)); // 4 random letters
        $numericPart = str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT); // 4 random digits

        return "{$prefix}-{$universityPart}-{$randomPart}-{$numericPart}";
    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //      ->logAll();
    // }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no');
    }
}
