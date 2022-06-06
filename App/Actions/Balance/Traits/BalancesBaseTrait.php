<?php

namespace App\Actions\Balance\Traits;

use App\Modules\balances\Repositories\BalancesRepository;

trait BalancesBaseTrait
{
    protected $balancesModel;

    public function __construct(BalancesRepository $balancesModel)
    {
        $this->balancesModel = $balancesModel;
    }
}