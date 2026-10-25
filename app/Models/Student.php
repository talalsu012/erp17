<?php

namespace App\Models;

use App\Models\attendances\Grouping;
use App\Models\attendances\GroupingStudent;
use App\Models\attendances\Lecture;
use App\Models\attendances\LectureStudent;
use App\Models\Cert\CertStudent;
use App\Models\Reg\Campus;
use App\Models\Reg\Card;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\MedicalFitnessDecisions;
use App\Models\Reg\Payment;
use App\Models\Reg\Special;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Reg\LtGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia;

    /** رمز «الحساب المسوّى» في مجموعة حساب الطالب (4002). */
    public const CLEARED_ACCOUNT_CODE = 4002001;

    use LogsActivity;

    protected $primaryKey = 'university_no'; // المفتاح الأساسي

    public $incrementing = false; // لأن university_no ليس auto-increment

    protected $keyType = 'string'; // أو bigint

    protected $table = 'students';

    protected $fillable = [
        'university_no',
        'name_ar',
        'name_en',
        'first_name',
        'second_name',
        'third_name',
        'fourth_name',
        'campus_code',
        'faculty_code',
        'department_code',
        'special_code',
        'level_code',
        'admission_code',
        'stady_code',
        'academic_year_code',
        'student_account',
        'status_code',
        'batch_code',
        'nationaltyno',
        'id_photo',
        'gender_code',
        'birth_place',
        'birth_date',
        'photo',
        'nationality_code',
        'state_code',
        'city',
        'localty',
        'block',
        'home_no',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'mobile',
        'social_media',
        'activites',
        'skill',
        'parent',
        'parent_alive',
        'parent_job',
        'parent_address',
        'parent_contacts',
        'mother',
        'mother_alive',
        'mother_job',
        'mother_address',
        'mother_contacts',
        'brothers',
        'currency',
        'installment_code',
        'discount_percent',
        'fees',
        'reg_fees',
        'fees1',
        'fees2',
        'fees3',
        'fees4',
        'fees5',
        'fees6',
        'fees7',
        'fees8',
        'fees9',
        'fees10',
        'pay1',
        'pay2',
        'pay3',
        'pay4',
        'all_fees',
        'pay',
        'xmod',
        'xstatus',
        'indexno',
        'note',
        'is_visible',
        'reg_access',
        'reg_access_at',
    ];

    protected $casts = [
        'reg_access' => \App\Reg\RegAccess::class,
        'reg_access_at' => 'datetime',
        'birth_date' => 'date',
        'email_verified_at' => 'datetime',
        'parent_alive' => 'boolean',
        'mother_alive' => 'boolean',
        'discount_percent' => 'integer',
        'fees' => 'double',
        'reg_fees' => 'double',
        'fees1' => 'double',
        'fees2' => 'double',
        'fees3' => 'double',
        'fees4' => 'double',
        'fees5' => 'double',
        'fees6' => 'double',
        'fees7' => 'double',
        'fees8' => 'double',
        'fees9' => 'double',
        'fees10' => 'double',
        'pay1' => 'double',
        'pay2' => 'double',
        'pay3' => 'double',
        'pay4' => 'double',
        'all_fees' => 'double',
        'pay' => 'double',
        'xmod' => 'double',
        'xstatus' => 'integer',
        'is_visible' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'birth_date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'university_no');
    }

    // العلاقات (Relationships) مثال
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_code', 'code');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_code', 'code');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_code', 'code');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'code');
    }

    public function special()
    {
        return $this->belongsTo(Special::class, 'special_code', 'code');
    }

    // reg_ltcolumns
    public function stady()
    {
        return $this->belongsTo(LtColumn::class, 'stady_code', 'code')
            ->where('grouped', LtGroup::Stady->value);
    }

    public function StudentAccount()
    {
        return $this->belongsTo(LtColumn::class, 'student_account', 'code')
            ->where('grouped', LtGroup::StudentAccount->value);
    }

    // public function accountStatus()
    // {
    //     return $this->belongsTo(LtColumn::class, 'student_account', 'code')
    //         ->where('grouped', LtGroup::StudentAccount->value);
    // }

    public function batch()
    {
        return $this->belongsTo(LtColumn::class, 'batch_code', 'code')
            ->where('grouped', LtGroup::AcademicYear->value);
    }

    /**
     * Get related LtColumn by field and grouped value.
     */
    public function ltColumn(string $field, int $grouped): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LtColumn::class, $field, 'code')
            ->where('grouped', $grouped);
    }

    public function admission()
    {
        return $this->belongsTo(LtColumn::class, 'admission_code', 'code')
            ->where('grouped', LtGroup::Admission->value);
    }

    public function academicYear()
    {
        return $this->belongsTo(LtColumn::class, 'academic_year_code', 'code')
            ->where('grouped', LtGroup::AcademicYear->value);
    }

    public function x_status()
    {
        return $this->belongsTo(LtColumn::class, 'xstatus', 'code')
            ->where('grouped', LtGroup::XStatus->value);
    }

    public function status()
    {
        return $this->belongsTo(LtColumn::class, 'status_code', 'code')
            ->where('grouped', LtGroup::Status->value);
    }

    public function nationality()
    {
        return $this->belongsTo(LtColumn::class, 'nationality_code', 'code')
            ->where('grouped', LtGroup::Nationality->value);
    }

    public function gender()
    {
        return $this->belongsTo(LtColumn::class, 'gender_code', 'code')
            ->where('grouped', LtGroup::Gender->value);
    }

    public function discountPercent()
    {
        return $this->belongsTo(LtColumn::class, 'discount_percent', 'code')
            ->where('grouped', LtGroup::Gender->value);
    }

    public function installment()
    {
        return $this->belongsTo(LtColumn::class, 'installment_code', 'code')
            ->where('grouped', LtGroup::Installment->value);
    }

    // يمكنك إضافة باقي العلاقات بنفس الطريقة
    // Associated
    public function subjects(): BelongsToMany
    {
        return $this->BelongsToMany(Subject::class, 'marks', 'university_no', 'subject_code')->withTimestamps();
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'university_no');
    }

    public function recom(): HasMany
    {
        return $this->HasMany(Recom::class, 'university_no', 'university_no');
    }

    public function getRecommendationNamesAttribute()
    {
        return $this->recom->map(fn ($item) => $item->recomName ? $item->recomName->name : null)->filter()->implode(', ');
    }

    public function grouping()
    {
        return $this->belongsToMany(Grouping::class, 'att_grouping_student', 'university_no', 'grouping_id')
            ->using(GroupingStudent::class)
            ->withPivot(['batch_code', 'room_id'])
            ->withTimestamps();
    }

    public function groupings()
    {
        return $this->belongsToMany(Grouping::class, 'att_grouping_student', 'university_no', 'grouping_id')
            ->using(GroupingStudent::class)
            ->withPivot(['batch_code', 'room_id'])
            ->withTimestamps();
    }

    // room name

    public function lectures()
    {
        return $this->belongsToMany(Lecture::class, 'att_lecture_student', 'university_no', 'lecture_id')
            ->withPivot('status')
            ->using(LectureStudent::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'state_code', 'code')
            ->where('grouped', LtGroup::State->value);
    }

    public function medicalFitness(): HasOne
    {
        return $this->hasOne(MedicalFitnessDecisions::class, 'university_no', 'university_no');
    }

    public function card(): HasOne
    {
        return $this->hasOne(Card::class, 'university_no', 'university_no')->latestOfMany('issue_date');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(CertStudent::class, 'universityno', 'university_no')->latestOfMany('alumia_date');
    }

    /**
     * هل الطالب مسوّى ماليّاً (شرط عرض النتائج في بوابة الطالب).
     */
    public function hasClearedAccount(): bool
    {
        return (int) $this->student_account === self::CLEARED_ACCOUNT_CODE;
    }

    /**
     * المتبقي من الرسوم (لا يقل عن صفر).
     */
    public function remainingFees(): float
    {
        return max(0.0, (float) $this->all_fees - (float) $this->pay);
    }

    /**
     * محتوى الصورة الشخصية الموحّد: النسخة الأساسية على القرص العام، ثم نسخة الحساب القديمة الخاصة.
     */
    public function photoContents(): ?string
    {
        $public = Storage::disk('public');

        if (filled($this->photo) && $public->exists($this->photo)) {
            return $public->get($this->photo);
        }

        $legacy = $this->portalUser?->avatar_url;
        $private = Storage::disk(config('student.photo_disk'));

        return filled($legacy) && $private->exists($legacy) ? $private->get($legacy) : null;
    }

    /**
     * بصمة الصورة الحالية (من وقت تعديل الملف) لتجديد ذاكرة المتصفح عند تغييرها.
     */
    public function photoVersion(): string
    {
        $public = Storage::disk('public');

        if (filled($this->photo) && $public->exists($this->photo)) {
            return dechex($public->lastModified($this->photo)).dechex($public->size($this->photo));
        }

        $legacy = $this->portalUser?->avatar_url;
        $private = Storage::disk(config('student.photo_disk'));

        return filled($legacy) && $private->exists($legacy) ? dechex($private->lastModified($legacy)) : '0';
    }

    /**
     * رابط الصورة للعرض في لوحة الإدارة (جداول وصفحات العرض). يمرّ عبر مسار محمي
     * فلا يعتمد على رابط storage العام، ويرجع الصورة الافتراضية عند غيابها.
     */
    public function photoUrl(): string
    {
        return $this->photoContents() === null
            ? self::defaultPhotoDataUri()
            : route('students.photo.show', [$this, 'v' => $this->photoVersion()]);
    }

    /**
     * الصورة كـ data URI للبطاقات والطباعة (FARGO) حتى لا تعتمد على رابط خارجي.
     */
    public function photoDataUri(): string
    {
        $contents = $this->photoContents();
        $mime = $contents !== null ? (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) : false;

        return is_string($mime) && str_starts_with($mime, 'image/')
            ? 'data:'.$mime.';base64,'.base64_encode($contents)
            : self::defaultPhotoDataUri();
    }

    public static function defaultPhotoDataUri(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><rect width="120" height="120" fill="#e5e7eb"/><circle cx="60" cy="46" r="22" fill="#9ca3af"/><path d="M16 112c0-24 20-40 44-40s44 16 44 40z" fill="#9ca3af"/></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function portalUser(): HasOne
    {
        return $this->hasOne(
            StudentAccount::class,
            'university_no',
            'university_no'
        );
    }
}
