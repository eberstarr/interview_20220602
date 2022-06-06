<?php

namespace App\Actions\Balance_logs\Traits;

use App\Modules\Balance_logs\Repositories\BalanceLogsRepository;

trait BalanceLogsBaseTrait
{
    protected $balancelogsModel;

    public function __construct(BalanceLogsRepository $balancelogsModel)
    {
        $this->balancelogsModel = $balancelogsModel;
    }
}