<?php

namespace App\Models\Invoice;

use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Reg\InvoiceStatus;
use Database\Factories\INV\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(InvoiceFactory::class)]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'name_ar',
        'invoice_no',
        'amount',
        'sys_type',
        'faculty_code',
        'description',
        'email',
        'phone',
        'bank_pay',
        'due_date',
        'expire_at',
        'status',
        'bank',
        'bankdate',
        'voucher_no',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'expire_at' => 'datetime',
        'bankdate' => 'datetime',
        'status' => InvoiceStatus::class,
    ];

    protected $dates = [
        'due_date',
        'expire_at',
        'bankdate',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_code', 'code');
    }

    public function systype()
    {
        return $this->belongsTo(LtColumn::class, 'sys_type', 'code')
            ->where('grouped', 4001);
    }
}
