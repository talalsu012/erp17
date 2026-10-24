<?php

namespace App\Models\Res;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * سبب يُختار لصف الدرجة (غياب، حرمان، بديل...). يحمل القيم التي تُنسخ إلى res_marks عند اختياره،
 * والقرار (التوصية) الذي يغلب قرار اللائحة.
 */
class Reson extends Model
{
    use SoftDeletes;

    protected $table = 'res_resons';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'code', 'name_ar', 'name_en', 'description', 'is_visible',
        'mark', 'grade', 'point', 'recom_code',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'int',
            'is_visible' => 'boolean',
            'mark' => 'int',
            'point' => 'float',
            'recom_code' => 'int',
        ];
    }

    public function recom(): BelongsTo
    {
        return $this->belongsTo(LtRecom::class, 'recom_code', 'code');
    }

    /**
     * هل يفرض السبب درجة/تقديراً/نقاطاً؟ إن لم يحدد أياً منها يبقى صف الدرجة كما هو.
     */
    public function appliesScore(): bool
    {
        return $this->mark !== null || filled($this->grade) || $this->point !== null;
    }
}
