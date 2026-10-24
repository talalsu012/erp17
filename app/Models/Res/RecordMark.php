<?php

namespace App\Models\Res;

use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سطر من السجل الأكاديمي المعتمد: نتيجة مادة لطالب كما اعتُمدت (نسخة ثابتة لا تتأثر بتعديل res_marks لاحقاً).
 */
class RecordMark extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'res_record_marks';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mark' => 'int',
            'cwork' => 'int',
            'point' => 'float',
            'crch' => 'float',
        ];
    }

    public function finalization(): BelongsTo
    {
        return $this->belongsTo(Finalization::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_code', 'code');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'batch_code', 'code')->where('grouped', 2003);
    }

    /**
     * سطور الطالب ضمن النسخ النشطة فقط (أساس الشهادات).
     */
    public function scopeApprovedFor(Builder $query, int|string $universityNo): Builder
    {
        return $query
            ->where('university_no', $universityNo)
            ->whereHas('finalization', fn (Builder $q) => $q->where('is_active', true));
    }
}
