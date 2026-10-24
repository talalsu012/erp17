<?php

namespace App\Models\Reg;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'reg_departments';

    protected $primaryKey = 'code';

    protected $fillable = [
        'code', 'name_ar', 'name_en', 'is_visible', 'faculty_code', 'results_published', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_visible' => 'boolean',
        'results_published' => 'boolean',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_code', 'code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'department_code', 'code');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reg_department_user', 'department_code', 'user_id');
    }

    /**
     * أقسام المستخدم المسموحة ضمن الكليات المسموحة له (نمط Faculty::forAuthUser).
     */
    public function scopeForAuthUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $departments = $user->allowedDepartmentCodes();
        $faculties = $user->allowedFacultyCodes();

        return $query
            ->when($departments !== null, fn (Builder $q) => $q->whereIn($q->getModel()->getQualifiedKeyName(), $departments))
            ->when($faculties !== null, fn (Builder $q) => $q->whereIn($q->getModel()->qualifyColumn('faculty_code'), $faculties));
    }
}
