<?php

namespace App\Models\Res;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * إجراء معالجة تشوّه لمادة: يحفظ الطريقة والمعاملات وقيم كل درجة قبل التعديل وبعده ليمكن التراجع عنه.
 */
class MarkCurve extends Model
{
    public const METHOD_RANGE = 'range';

    public const METHOD_STDDEV = 'stddev';

    protected $table = 'res_mark_curves';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'reverted_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MarkCurveItem::class, 'mark_curve_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    public function isReverted(): bool
    {
        return $this->reverted_at !== null;
    }
}
