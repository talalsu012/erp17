<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldPermission extends Model
{
    protected $table = 'fields';

    protected $fillable = [
        'name',
        'description',
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RoleFieldPermission::class);
    }
}
