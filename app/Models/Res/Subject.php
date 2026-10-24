<?php

namespace App\Models\Res;

use App\Models\attendances\Staff;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Student;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subject extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    #[Scope]
    protected function Active(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function plans(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'plan');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_code');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_code');
    }

    public function special(): BelongsTo
    {
        return $this->belongsTo(Special::class, 'special_code');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_code');
    }

    public function types(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'type');
    }

    // studets_marks
    public function Students(): BelongsToMany
    {
        return $this->BelongsToMany(Student::class, 'marks', 'subject_code', 'university_no')->withTimestamps();
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'att_staff_subject', 'subject_code', 'staff_id');
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_code', 'code');
    }

    /**
     * Children collection for this ltcolumn
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_code', 'code');
    }
}
