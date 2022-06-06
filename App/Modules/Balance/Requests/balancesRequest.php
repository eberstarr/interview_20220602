<?php

namespace App\Modules\Balance\Requests;

use App\Modules\Base\Requests\BaseRequest;
use App\Modules\balances\balances;

class balancesRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'payer_user_id' => 'bail|required|integer',
            'payee_user_id' => 'bail|required|integer',
            'transaction_type' => 'bail|required|string|in:CREDIT, DEBIT',
            'amount' => 'bail|required|integer',
        ];
    }
}