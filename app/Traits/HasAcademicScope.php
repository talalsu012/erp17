<?php

namespace App\Traits;

use App\Models\Reg\Department;
use App\Models\Reg\Level;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * نطاق المستخدم الأكاديمي: الكليات والأقسام والمستويات المسموحة.
 *
 * الكلية: بلا تعيين = لا شيء (كما كان). القسم والمستوى: بلا تعيين = بلا قيد،
 * حتى لا يُحرم المستخدمون الحاليون من الوصول عند تفعيل الميزة، وبوجود تعيين يُقيَّد بالمعيَّن فقط.
 * المدير الأعلى أو من يملك صلاحية view_all_* يرى الكل.
 */
trait HasAcademicScope
{
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'reg_department_user', 'user_id', 'department_code')->withTimestamps();
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'reg_level_user', 'user_id', 'level_code')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('filament-shield.super_admin.name', 'super_admin'));
    }

    /**
     * @return Collection<int, int|string>|null null = كل الكليات
     */
    public function allowedFacultyCodes(): ?Collection
    {
        return once(fn (): ?Collection => $this->seesAll('view_all_faculties')
            ? null
            : $this->faculties()->pluck('reg_faculties.code'));
    }

    /**
     * @return Collection<int, int|string>|null null = بلا قيد على الأقسام
     */
    public function allowedDepartmentCodes(): ?Collection
    {
        return once(fn (): ?Collection => $this->assignedCodes('view_all_departments', $this->departments(), 'reg_departments.code'));
    }

    /**
     * @return Collection<int, int|string>|null null = بلا قيد على المستويات
     */
    public function allowedLevelCodes(): ?Collection
    {
        return once(fn (): ?Collection => $this->assignedCodes('view_all_levels', $this->levels(), 'reg_levels.code'));
    }

    /**
     * هل يصل المستخدم إلى هذا السياق؟ القسم والمستوى الفارغان يعنيان "غير محدد" فلا يُرفضان هنا،
     * ويبقى تقييد الاستعلامات عبر constrainToAcademicScope.
     */
    public function canAccessAcademic(mixed $faculty, mixed $department = null, mixed $level = null): bool
    {
        $allowedFaculties = $this->allowedFacultyCodes();

        if ($allowedFaculties !== null && (blank($faculty) || ! $this->codeIn($allowedFaculties, $faculty))) {
            return false;
        }

        return $this->unrestrictedOrContains($this->allowedDepartmentCodes(), $department)
            && $this->unrestrictedOrContains($this->allowedLevelCodes(), $level);
    }

    /**
     * يقيّد استعلاماً على جدول فيه أعمدة الكلية والقسم والمستوى بنطاق المستخدم.
     *
     * @param  array{faculty?: string, department?: string, level?: string}  $columns
     * @param  list<'department'|'level'>  $includeUnassigned  أبعاد تبقى فيها الصفوف غير المعيَّنة (NULL) ظاهرة، كالمواد العامة
     * @param  string|null  $studentKey  عمود رقم الطالب: الصف بلا قسم (درجة مادة عامة) يظهر فقط إن كان طالبه في قسم مسموح
     */
    public function constrainToAcademicScope(Builder $query, array $columns = [], array $includeUnassigned = [], ?string $studentKey = null): Builder
    {
        $columns += ['faculty' => 'faculty_code', 'department' => 'department_code', 'level' => 'level_code'];

        foreach ([
            'faculty' => $this->allowedFacultyCodes(),
            'department' => $this->allowedDepartmentCodes(),
            'level' => $this->allowedLevelCodes(),
        ] as $dimension => $codes) {
            if ($codes === null) {
                continue;
            }

            $column = $columns[$dimension];

            if ($dimension === 'department' && $studentKey !== null) {
                $query->where(fn (Builder $q) => $q->whereIn($column, $codes)->orWhere(
                    fn (Builder $unassigned) => $unassigned->whereNull($column)->whereIn(
                        $studentKey,
                        Student::query()->whereIn('department_code', $codes)->select('university_no'),
                    ),
                ));
            } elseif (in_array($dimension, $includeUnassigned, true)) {
                $query->where(fn (Builder $q) => $q->whereIn($column, $codes)->orWhereNull($column));
            } else {
                $query->whereIn($column, $codes);
            }
        }

        return $query;
    }

    private function seesAll(string $permission): bool
    {
        return $this->isSuperAdmin() || $this->can($permission);
    }

    /**
     * @return Collection<int, int|string>|null
     */
    private function assignedCodes(string $permission, BelongsToMany $relation, string $column): ?Collection
    {
        if ($this->seesAll($permission)) {
            return null;
        }

        $codes = $relation->pluck($column);

        return $codes->isEmpty() ? null : $codes;
    }

    private function unrestrictedOrContains(?Collection $allowed, mixed $code): bool
    {
        return $allowed === null || blank($code) || $this->codeIn($allowed, $code);
    }

    private function codeIn(Collection $codes, mixed $code): bool
    {
        return $codes->map(fn ($value): string => (string) $value)->contains((string) $code);
    }
}
