<?php

namespace App\Modules\Balance;

use App\Modules\Base\Models;

class balances extends Models
{
    protected $table = 'balances';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

}
