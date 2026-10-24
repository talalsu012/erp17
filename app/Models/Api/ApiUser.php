<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * مستخدم واجهة البنك (جدول usersapi) كما في النظام القديم apiBOK.
 */
class ApiUser extends Model
{
    public $timestamps = false;

    protected $table = 'usersapi';

    protected $fillable = ['name', 'email', 'password', 'bank', 'university', 'ip'];

    protected $hidden = ['password'];
}
