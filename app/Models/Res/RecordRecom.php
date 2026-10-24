<?php

namespace App\Models\Res;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * توصية الطالب كما اعتُمدت (المعدل والإحصائيات والقرار) ضمن السجل الأكاديمي.
 */
class RecordRecom extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'res_record_recoms';

    protected $guarded = [];

    public function finalization(): BelongsTo
    {
        return $this->belongsTo(Finalization::class);
    }
}
