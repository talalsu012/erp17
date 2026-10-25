<?php

namespace App\Models\Res;

use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Student;
use App\Reg\AbsStatus;
use App\Reg\Semester;
use App\Traits\GuardsFinalizedRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recom extends Model
{
    use GuardsFinalizedRecords, HasFactory, SoftDeletes;

    // Explicitly define the table name if it doesn't follow Laravel's pluralization convention
    protected $table = 'res_recoms';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'semester' => Semester::class,
            'examType' => AbsStatus::class,
            'is_visible' => 'boolean',
            'is_locked' => 'boolean',
            'is_released' => 'boolean',
            'point' => 'float',
        ];
    }

    public function recomName(): BelongsTo
    {
        return $this->belongsTo(LtRecom::class, 'recom_code', 'code');
    }

    public function student(): BelongsTo
    {
        // 'university_no' is the foreign key on 'res_marks' table
        // 'university_no' is the local key on 'students' table
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

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
    public function batch(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'batch_code', 'code')
            ->where('grouped', 2003);
    }
}
