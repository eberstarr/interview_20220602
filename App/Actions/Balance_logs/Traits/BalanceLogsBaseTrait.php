<?php

namespace App\Actions\balance_logs\Traits;

use App\Modules\balance_logs\Repositories\BalanceLogsRepository;

trait BalanceLogsBaseTrait
{
    protected $balancelogsModel;

    public function __construct(BalanceLogsRepository $balancelogsModel)
    {
        $this->balancelogsModel = $balancelogsModel;
    }
}