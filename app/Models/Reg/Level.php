<?php

namespace App\Models\Reg;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Level extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $primaryKey = 'code';

    protected $table = 'reg_levels';

    protected $fillable = [
        'code', 'name_ar', 'name_en', 'parent_code', 'is_visible',
    ];

    public function student()
    {
        return $this->hasMany(Student::class, 'level_code', 'code');
    }

    public function parent()
    {
        return $this->belongsTo(Level::class, 'parent_code', 'code');
    }

    public function children()
    {
        return $this->hasMany(Level::class, 'parent_code', 'code');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reg_level_user', 'level_code', 'user_id');
    }

    /**
     * مستويات المستخدم المسموحة (نمط Faculty::forAuthUser).
     */
    public function scopeForAuthUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $levels = $user->allowedLevelCodes();

        return $query->when($levels !== null, fn (Builder $q) => $q->whereIn($q->getModel()->getQualifiedKeyName(), $levels));
    }
}
