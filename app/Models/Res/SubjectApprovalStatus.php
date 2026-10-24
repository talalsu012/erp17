<?php

// app/Models/SubjectApprovalStatus.php

namespace App\Models\Res;

use App\Models\Reg\LtColumn;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectApprovalStatus extends Model
{
    use SoftDeletes;

    protected $table = 'subject_approval_statuses';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'current_stage' => MarkApprovalStage::class,
        ];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    // batch_code
    public function batch()
    {
        return $this->belongsTo(LtColumn::class, 'batch_code', 'code')
            ->where('grouped', 2003);
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }
}
