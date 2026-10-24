<?php

namespace App\Models\Reg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpenReg extends Model
{
    use SoftDeletes;

    protected $table = 'reg_open_reg';

    protected $fillable = [
        'faculty_code',
        'department_code',
        'level_code',
        'batch_code',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relationship to Faculty
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_code', 'code');
    }

    /**
     * Relationship to Department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_code', 'code');
    }

    /**
     * Relationship to Level
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_code', 'code');
    }

    /**
     * Relationship to Batch
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'batch_code', 'code');
    }
}
