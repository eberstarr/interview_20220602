<?php

namespace App\Actions\Balance;

use App\Modules\Balance\Requests\balancesRequest;
use App\Actions\Balance\Traits\BalancesBaseTrait;
use Illuminate\Http\Request;
use App\Facades\ApiResponse;

use App\Actions\Balance_logs\BalanceLogsCreate;

class BalanceUpdate
{
    use BalancesBaseTrait;

    public function action(balancesRequest $request)
    {
        $response = $this->execute($request->data);
        
        return $response ?
            ApiResponse::success(null, 'Update Balance successful.'):
            ApiResponse::failed(null, 'Update Balance failed.');
    }

    public function execute(array $params)
    {
        $formatterData = $this->formatterData($params);

        $user['payer'] = data_get($params, 'payer_user_id');
        $user['payee'] = data_get($params, 'payee_user_id');

        foreach ($user as $key => $user_id) {
            $balances = $this->balancesModel->where('user_id', $user_id);

            $response = $this->balancesModel->where('user_id', $user_id)->update(
                $this->formatterData(
                    $params,
                    $balances,
                    $key
                )
            );

            if($response){
                $response = app(BalanceLogsCreate::class)->action(
                    $this->formatterData_logs(
                        $params,
                        $user_id,
                        $key
                    )
                );
            } else {
                return false;
            }
        }
        
        return true;

    }

    public function formatterData(array $params, $balances, $user_type)
    {
        $balance = data_get($balances, 'balance');
        $amount = data_get($params, 'amount');

        return [
            'user_id' => data_get($params, 'user_id'),
            'balance' => ($user_type == 'payer') ? $balance+$amount: $balance-$amount,
        ];
    }

    public function formatterData_logs(array $params, $user_id, $user_type)
    {
        return [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'transaction_type' => data_get($params, 'transaction_type'),
            'amount' => data_get($params, 'amount'),
        ];
    }
}