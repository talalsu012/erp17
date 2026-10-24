<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(
            FieldPermission::class,
            'role_field_permissions', // اسم جدول الربط
            'role_id',               // المفتاح الأجنبي لمودل Role
            'field_id'    // تأكد أن هذا الاسم يطابق العمود الموجود فعلياً في DB
        );
    }
}
