<?php

namespace App\Models\Reg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campus extends Model
{
    use SoftDeletes;

    protected $table = 'reg_campuses';

    protected $primaryKey = 'code';

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }
}
