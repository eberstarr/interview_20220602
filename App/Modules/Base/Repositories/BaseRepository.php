<?php

namespace App\Modules\Base\Repositories;

use App\Facades\RedisHelper;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    public function all($columns = ['*'])
    {
        return $this->model
            ->all($columns);
    }

    public function create($attributes)
    {
        return $this->model
            ->create($attributes);
    }

    public function multipleInsert(array $attributes)
    {
        return DB::transaction(function () use ($attributes) {
            return $this->model->insert($attributes);
        },3);
    }

    public function getSeqDB($type = 1)
    {
        $lockKey = ($type === 1) ?
            ('erp:order_seq'):
            (
                ($type === 2) ?
                ('erp:billing_seq'):
                ('erp:shipments_seq')
            );

        while (true) {
            if (RedisHelper::lock($lockKey)) {
                $seq = DB::connection('seq_db')
                    ->select(
                        DB::raw("select(currval_erp({$type}, 1)) as seq")
                    );

                RedisHelper::unlock($lockKey);
                return $seq[0]->seq;
            }

            usleep(100);
        }
    }

    public function paginate($per_page = 50, $sort_column = 'id', $sort = 'DESC')
    {
        return $this->model
            ->orderBy($sort_column, $sort)
            ->paginate($per_page);
    }

    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    public function get($columns = ['*'])
    {
        return $this->model
            ->get($columns);
    }

    public function getBy($column, $value, $columns = ['*'])
    {
        return $this->model
            ->where($column, $value)
            ->get($columns);
    }

    public function getByMore($conditions, $columns = ['*'])
    {
        return $this->model
            ->where($conditions)
            ->get($columns);
    }

    public function getWhereInBy($column, $value, $columns = ['*'])
    {
        return $this->model
            ->whereIn($column, $value)
            ->get($columns);
    }

    public function getActiveWhereInBy($column, $value, $columns = ['*'])
    {
        return $this->model
            ->whereIn($column, $value)
            ->where('active', 1)
            ->get($columns);
    }

    public function getWithBy($column, $value, $with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->where($column, $value)
            ->get($columns);
    }

    public function getWithWhereInBy($column, $value, $with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->whereIn($column, $value)
            ->get($columns);
    }

    public function getActiveBy($column, $value, $columns = ['*'])
    {
        return $this->model
            ->where($column, $value)
            ->where('active', 1)
            ->get($columns);
    }

    public function getActiveWith($with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->where('active', 1)
            ->get($columns);
    }

    public function getActiveWithBy($column, $value, $with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->where($column, $value)
            ->where('active', 1)
            ->get($columns);
    }

    public function getActiveWithWhereInBy($column, array $value, $with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->whereIn($column, $value)
            ->where('active', 1)
            ->get($columns);
    }

    public function first($columns = ['*'])
    {
        return $this->model
            ->first($columns);
    }

    public function firstByWithLock($column, $value, $columns = ['*'])
    {
        return $this->model
            ->where($column, $value)
            ->lockForUpdate()
            ->first($columns);
    }

    public function firstBy($column, $value, $columns = ['*'])
    {
        return $this->model
            ->where($column, $value)
            ->first($columns);
    }

    public function firstWithBy($column, $value, $with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->where($column, $value)
            ->first($columns);
    }

    public function firstActiveBy($column, $value, $columns = ['*'])
    {
        return $this->model
            ->where($column, $value)
            ->where('active', 1)
            ->first($columns);
    }

    public function firstActiveWithBy($column, $value, $with = [], $columns = ['*'])
    {
        return $this->model
            ->with($with)
            ->where($column, $value)
            ->where('active', 1)
            ->first($columns);
    }

    public function batchUpdate(array $values, $index = 'id', $table = null, $addQuery = null)
    {
        try {
            $table = $table ?? $this->model->getTable();
            $final  = array();
            $ids    = array();
            
            if(!count($values)) {
                return false;
            }
            
            foreach ($values as $key => $val) {
                $ids[] = $val[$index];
                foreach (array_keys($val) as $field) {
                    if ($field !== $index) {
                        $value = (is_null($val[$field]) ? 'NULL' : '"' . mysql_escape_insert($val[$field]) . '"');
                        $final[$field][] = 'WHEN `'. $index .'` = "' . $val[$index] . '" THEN ' . $value . ' ';
                    }
                }
            }
            
            $cases = '';
            
            foreach ($final as $k => $v) {
                $cases .=  '`'. $k.'` = (CASE '. implode("\n", $v) . "\n"
                    . 'ELSE `'.$k.'` END), ';
            }
            
            $query = "UPDATE `$table` SET " . substr($cases, 0, -2) .
                " WHERE `$index` IN(" . '"' . implode('","', $ids) . '"' . ")$addQuery;";
            
            return DB::transaction(function () use ($query) {
                return DB::statement($query);
            }, 3);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getActiveWithSingleModelBy(
        $column,
        array $value,
        string $with,
        $localColumns = ['*'])
    {
        $withModel = strpos($with, ':') !== false ?
            explode(':', $with)[0]:
            $with;
        return $this->model
            ->with($with)
            ->whereHas($withModel, function ($query) use (
                $column,
                $value) {
                $query->whereIn($column, $value)
                    ->where('active', 1);
            })->get($localColumns);
    }

    public function destroyWithTrashed($history_table, int $subdays)
    {
        return DB::transaction(function () use ($history_table, $subdays) {
            $this->model
                ->where('active', 0)
                ->whereDate('deleted_at', '<=', now()->subDays($subdays))
                ->chunk(500, function ($items) use ($history_table) {
                    if ($result = DB::table($history_table)->insert($items->toArray())) {
                        $this->model->destroy(
                            $items->pluck('id')->toArray()
                        );
                    }
                });
        });
    }
}
