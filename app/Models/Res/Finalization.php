<?php

namespace App\Models\Res;

use App\Models\User;
use Database\Factories\Res\FinalizationFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * اعتماد نتائج مجموعة (كلية + قسم + مستوى + دفعة). كل نسخة لقطة موقّعة؛ النشطة هي أساس الشهادات.
 */
#[UseFactory(FinalizationFactory::class)]
class Finalization extends Model
{
    use HasFactory;

    public const KIND_APPROVAL = 'approval';

    public const KIND_AMENDMENT = 'amendment';

    protected $table = 'res_finalizations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
            'version' => 'int',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(RecordMark::class, 'finalization_id');
    }

    public function recoms(): HasMany
    {
        return $this->hasMany(RecordRecom::class, 'finalization_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  array{faculty: mixed, department: mixed, level: mixed, batch: mixed}  $cohort
     */
    public function scopeForCohort(Builder $query, array $cohort): Builder
    {
        return $query
            ->where('faculty_code', $cohort['faculty'])
            ->where('department_code', $cohort['department'])
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch']);
    }

    public function isAmendment(): bool
    {
        return $this->kind === self::KIND_AMENDMENT;
    }
}
