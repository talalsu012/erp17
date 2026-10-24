<?php

// app/Models/Cert/CertStyle.php

namespace App\Models\Cert;

use Illuminate\Database\Eloquent\Model;

class CertStyle extends Model
{
    protected $table = 'cert_style';

    protected $fillable = [
        'key',
        'value',
        'group',
        'cert',
    ];
    // Just2(*)admin#

    public $timestamps = true;
}
