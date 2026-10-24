<?php

namespace App\Models\Reg;

use App\Models\Student;
use App\Models\User;
use App\Reg\AdmissionStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionWorkflow extends Model
{
    protected $table = 'admission_workflows';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'stage' => AdmissionStage::class,
            'assigned_fees' => 'decimal:2',
            'medical_at' => 'datetime',
            'committee_at' => 'datetime',
            'fees_at' => 'datetime',
            'paid_at' => 'datetime',
            'account_created_at' => 'datetime',
            'dean_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
