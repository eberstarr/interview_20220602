<?php

namespace App\Modules\Balance_logs\Repositories;

use App\Modules\Base\Repositories\BaseRepository;
use App\Modules\Balance_logs\balance_logs;

class BalanceLogsRepository extends BaseRepository
{
    protected $model;

    public function __construct(balance_logs $model)
    {
        $this->model = $model;
    }

    public function create($attributes)
    {
        return $this->model->create($attributes);
    }
}