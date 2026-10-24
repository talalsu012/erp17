<?php

namespace App\Models\Res;

use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Student;
use App\Reg\MarkApprovalStage;
use App\Reg\MarkApprovalStatus;
use App\Traits\GuardsFinalizedRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mark extends Model
{
    use GuardsFinalizedRecords, HasFactory, SoftDeletes;
    // use \OwenIt\Auditing\Auditable;

    // Explicitly define the table name if it doesn't follow Laravel's pluralization convention
    protected $table = 'res_marks';

    // المفتاح الفريد: university_no + subject_code + level_code + batch_code + semester + examType
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'approval_stage' => MarkApprovalStage::class,
            'approval_status' => MarkApprovalStatus::class,
            'approved_at' => 'datetime',
            'is_visible' => 'boolean',
            'is_locked' => 'boolean',
            'is_released' => 'boolean',
            'is_recheck' => 'boolean',
            'is_recheck_passed' => 'boolean',
            'point' => 'float',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the student that owns the mark.
     */
    public function student(): BelongsTo
    {
        // 'university_no' is the foreign key on 'res_marks' table
        // 'university_no' is the local key on 'students' table
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

    /**
     * Get the subject that owns the mark.
     */
    public function subject(): BelongsTo
    {
        // 'subject_code' is the foreign key on 'res_marks' table
        // 'code' is the local key on 'subjects' table
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    /**
     * Get the faculty that owns the mark.
     */
    public function faculty(): BelongsTo
    {
        // 'faculty_code' is the foreign key on 'res_marks' table
        // 'code' is the local key on 'faculties' table
        return $this->belongsTo(Faculty::class, 'faculty_code', 'code');
    }

    /**
     * Get the level that owns the mark.
     */
    public function level(): BelongsTo
    {
        // 'level_code' is the foreign key on 'res_marks' table
        // 'code' is the local key on 'levels' table
        return $this->belongsTo(Level::class, 'level_code', 'code');
    }

    /**
     * Get the batch that owns the mark.
     */
    public function batch()
    {
        return $this->belongsTo(LtColumn::class, 'batch_code', 'code')
            ->where('grouped', 2003);
    }

    /**
     * Get the reason (res_reson) for the mark, if any.
     */
    public function reason(): BelongsTo
    {
        // 'reson_code' is the foreign key on 'res_marks' table
        // 'code' is the local key on 'res_resons' table
        return $this->belongsTo(Reson::class, 'reson_code', 'code');
    }
}
