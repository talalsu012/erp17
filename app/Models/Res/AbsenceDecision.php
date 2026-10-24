<?php

namespace App\Models\Res;

use App\Models\Student;
use App\Models\User;
use App\Reg\AbsenceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceDecision extends Model
{
    protected $table = 'res_absence_decisions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => AbsenceStatus::class,
            'absence_pct' => 'float',
            'warned_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
