<?php

namespace App\Actions\Balance_logs;

use App\Actions\balance_logs\Traits\BalanceLogsBaseTrait;
use Illuminate\Http\Request;
use App\Facades\ApiResponse;

class BalanceLogsCreate
{
    use BalanceLogsBaseTrait;

    public function action(Request $request)
    {
        $response = $this->execute($request->data);
        
        return $response ?
            ApiResponse::success(null, 'Create Balance Logs successful.'):
            ApiResponse::failed(null, 'Create Balance Logs failed.');

    }

    public function execute(array $params)
    {
        $formatterData = $this->formatterData($params);

        if($create = $this->balancelogsModel->create($formatterData)){
            return true;
        }

        return false;
    }

    public function formatterData(array $params)
    {
        return [
            'user_id' => data_get($params, 'user_id'),
            'transaction_type' => data_get($params, 'transaction_type'),
            'amount' => data_get($params, 'amount'),
        ];
    }
}