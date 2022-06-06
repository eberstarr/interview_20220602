<?php

namespace App\Modules\Balance_logs;

use App\Modules\Base\Models;

class balance_logs extends Models
{
    protected $table = 'balance_logs';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'transaction_type',
        'amount',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

}