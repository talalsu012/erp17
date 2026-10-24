<?php

namespace App\Models\Reg;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalFitnessDecisions extends Model
{
    protected $table = 'medical_fitness_decisions';

    protected $primaryKey = 'university_no';

    public $incrementing = false;

    protected $fillable = [
        'university_no',
        'medical_fitness',
        'college_interview',
        'final_decision',
        'medical_notes',
        'interview_notes',
        'rejection_reason',
        'decision_date',
        'decided_by',
    ];

    protected $casts = [
        'decision_date' => 'date',
    ];

    /**
     * العلاقة مع الطالب
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

    /**
     * العلاقة مع المستخدم الذي اتخذ القرار
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Scope للطلاب المقبولين
     */
    public function scopeAccepted($query)
    {
        return $query->where('final_decision', 'accepted');
    }

    /**
     * Scope للطلاب المرفوضين
     */
    public function scopeRejected($query)
    {
        return $query->where('final_decision', 'rejected');
    }

    /**
     * Scope للطلاب المؤهلين طبياً
     */
    public function scopeMedicallyFit($query)
    {
        return $query->where('medical_fitness', 'fit');
    }
}
