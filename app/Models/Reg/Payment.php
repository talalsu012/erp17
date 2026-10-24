<?php

namespace App\Models\Reg;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'payments';

    protected $fillable = [
        'university_no',
        'amount',
        'currency',
        'bankdate',
        'bank',
        'branch',
        'voucher_no',
        'batch_code',
        'level_code',
        'sys_type',
        'transaction_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'batch_code');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_code');
    }
}
