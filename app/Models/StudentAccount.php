<?php

namespace App\Models;

use App\Notifications\Student\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * حساب بوابة الطالب (جدول users_std). يستعمله الـ guard «student».
 */
class StudentAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'users_std';

    /** @var list<string> */
    protected $fillable = [
        'name', 'name_en', 'email', 'password', 'university_no', 'avatar_url',
        'school', 'percent', 'nationaltyno', 'birth_place', 'birth_date',
        'nationality_code', 'state_code', 'city', 'localty', 'block', 'home_no',
        'mobile', 'social_media', 'activites', 'skill', 'parent', 'parent_alive',
        'parent_job', 'parent_address', 'parent_contacts', 'mother', 'mother_alive',
        'mother_job', 'mother_address', 'mother_contacts', 'brothers', 'files',
    ];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'parent_alive' => 'boolean',
            'mother_alive' => 'boolean',
            'files' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

    /**
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
