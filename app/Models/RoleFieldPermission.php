<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleFieldPermission extends Model
{
    //
    protected $table = 'role_field_permissions';

    protected $fillable = [
        'role_id',
        'field_id',
        'can_create',
        'can_update',
    ];

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function field()
    {
        return $this->belongsTo(FieldPermission::class);
    }
}
