<?php

namespace App\Models\Reg;

use App\Reg\LtGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LtColumn extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'reg_ltcolumns';

    protected $primaryKey = 'code';

    protected $fillable = [
        'code', 'name_ar', 'name_en', 'grouped', 'is_visible', 'group_slug', 'description',
        'parent', // new hierarchy column
    ];

    /**
     * Parent ltcolumn relation (self-referential)
     */
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

    /**
     * @param  Builder<LtColumn>  $query
     * @return Builder<LtColumn>
     */
    public function scopeInGroup(Builder $query, LtGroup $group): Builder
    {
        return $query->where('grouped', $group->value);
    }

    /**
     * خيارات القائمة المنسدلة لمجموعة مرجعية: code => name_ar.
     *
     * @return Collection<int|string, string>
     */
    public static function optionsFor(LtGroup $group): Collection
    {
        return static::query()->inGroup($group)->pluck('name_ar', 'code');
    }
}
