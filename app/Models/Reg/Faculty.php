<?php

namespace App\Models\Reg;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Faculty extends Model implements HasMedia
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use InteractsWithMedia;

    protected $primaryKey = 'code';

    protected $table = 'reg_faculties';

    protected $fillable = [
        'code', 'name_ar',
        'name_en', 'is_visible',
        'campus_code', 'start_reg', 'end_reg', 'results_published', 'primary_hex',
        'metadata', 'logo', 'results_approver_id',
    ];

    protected $casts = [
        'metadata' => 'json',
        'primary_hex' => 'string',
        'logo' => 'string',
    ];

    protected static function booted()
    {
        // هذه الدالة تتفعل تلقائياً بعد نجاح أي عملية تحديث على الكلية
        static::updated(function ($faculty) {

            // التحقق مما إذا كان التحديث قد شمل تواريخ التسجيل
            if ($faculty->wasChanged(['start_reg', 'end_reg'])) {

                // تنفيذ استعلام مباشر لتحديث جميع طلاب هذه الكلية دفعة واحدة
                \Illuminate\Support\Facades\DB::statement('
                    UPDATE students 
                    SET pay = CalculateEarlyPayment(university_no) 
                    WHERE faculty_code = ?
                ', [$faculty->code]);

            }
        });
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_code', 'code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'faculty_code', 'code');
    }

    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            Student::class,
            'faculty_code',   // المفتاح الأجنبي في جدول الطلاب
            'university_no',  // المفتاح الأجنبي في جدول المدفوعات
            'code',           // المفتاح الأساسي في جدول الكليات
            'university_no'   // المفتاح الأساسي في جدول الطلاب
        );
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'reg_faculty_user', 'faculty_code', 'user_id');
    }

    // App\Models\Reg\Faculty.php

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        // إذا لم يكن هناك مستخدم مسجل دخول، لا تعيد أي شيء
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // إذا كان المستخدم "سوبر أدمن" أو لديه صلاحية رؤية كل الكليات
        // يمكنك تعديل هذا الشرط حسب مسمى الصلاحية لديك
        if ($user->hasRole('super_admin') || $user->can('view_all_faculties')) {
            return $query;
        }

        // جلب الكليات المرتبطة بالمستخدم فقط عبر الجدول الوسيط
        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }
}
