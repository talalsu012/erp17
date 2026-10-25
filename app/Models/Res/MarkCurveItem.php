<?php

namespace App\Models\Res;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarkCurveItem extends Model
{
    protected $table = 'res_mark_curve_items';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'old_point' => 'float',
            'new_point' => 'float',
        ];
    }

    public function curve(): BelongsTo
    {
        return $this->belongsTo(MarkCurve::class, 'mark_curve_id');
    }

    public function mark(): BelongsTo
    {
        return $this->belongsTo(Mark::class);
    }
}
