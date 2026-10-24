<?php

namespace App\Models\Cert;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CertStudent extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'gender_code',
        'admission_code',
        'academic_year_code',
        'stady_code',
        'department_code',
        'special_code',
        'universityno',
        'name_ar',
        'name_en',
        'alumia_date',
        'degree_id',
        'grade_id',
        'faculty_code',
        'nationalty_code',
        'meeting_no',
        'remarks',
        'remarks_en',
        'hijri_date',
        'slug',
        'verify_hash',
    ];

    protected $casts = [
        'alumia_date' => 'date',
        'is_visible' => 'boolean',
    ];

    // Generate verify hash for the certificate
    protected static function boot()
    {
        parent::boot();

        // 1. عند إنشاء طالب جديد
        static::creating(function ($student) {
            $student->generateVerifyHash();
        });

        // 2. عند تحديث بيانات الطالب (إذا تغيرت البيانات الأساسية التي نعتمد عليها في التشفير)
        static::updating(function ($student) {
            if ($student->isDirty(['name_ar', 'faculty_code', 'alumia_date'])) {
                $student->generateVerifyHash();
            }
        });
    }

    // الدالة المسؤولة عن توليد الكود وتم إزالة save() منها حتى لا تسبب تكرار
    public function generateVerifyHash(): void
    {
        // تأكد من تحويل التاريخ إلى نص لتجنب أخطاء الدمج مع Carbon
        $date = $this->alumia_date instanceof \Carbon\Carbon
                ? $this->alumia_date->toDateString()
                : clone $this->alumia_date;

        // توليد الـ Hash
        $this->verify_hash = hash(
            'sha256',
            $this->name_ar.$this->faculty_code.$date.config('app.key')
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    /* ================= Accessors ================= */

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getYearAttribute(): ?string
    {
        return $this->alumia_date?->format('Y');
    }

    /* ================= Relations ================= */

    public function degree(): BelongsTo
    {
        return $this->belongsTo(CertSetting::class, 'degree_id')
            ->where('groups', '=', 'degree');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(CertSetting::class, 'grade_id')
            ->where('groups', '=', 'grade');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(CertSetting::class, 'admission_code')
            ->where('groups', '=', 'admission');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_code');
    }

    /**
     * Get the options for logging activity.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function special(): BelongsTo
    {
        return $this->belongsTo(Special::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'gender_code')
            ->where('grouped', '=', '1001');
    }

    public function stady(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'stady_code');
    }

    public function academicyear(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'academic_year_code');
    }

    public function nationalty(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'nationalty_code');
    }
}
