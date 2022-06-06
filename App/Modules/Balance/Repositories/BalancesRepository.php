<?php

namespace App\Modules\Balance\Repositories;

use App\Modules\Base\Repositories\BaseRepository;
use App\Modules\Balance\balances;

class BalancesRepository extends BaseRepository
{
    protected $model;

    public function __construct(balances $model)
    {
        $this->model = $model;
    }

    public function updateBy($conditions, $attributes)
    {
        return $this->model->where($conditions)->update($attributes);
    }
}