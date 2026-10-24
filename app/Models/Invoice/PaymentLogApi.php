<?php

namespace App\Models\Invoice;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentLogApi extends Model
{
    protected $table = 'payment_log_apis';

    protected $fillable = [
        'invoice_no',
        'name',
        'amount',
        'bankdate',
        'voucher_no',
        'bank',
        'branch',
        'user_id',
        'response_msg',
        'response_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
