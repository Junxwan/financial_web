<?php

namespace App\Repositories;

use App\Models\Equity;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReportRepository extends Repository
{
    public function list(array $data)
    {
        $query = Report::query()->select(
            DB::raw('reports.id'),
            'date', 'title', 'code', 'name', 'price_f', 'action', 'eps_1', 'eps_2', 'eps_3', 'eps_4', 'pe'
        )->join('stocks', 'reports.stock_id', '=', 'stocks.id');
        $queryTotal = Report::query();

        if (isset($data['search'])) {
            $search = $data['search'];
            if (isset($search['value']) && ! empty($search['value'])) {
                switch ($search['name']) {
                    case 'code':
                        $query = $this->whereCode($query, $search['value']);
                        $queryTotal = $this->whereCode($queryTotal, $search['value'])
                            ->join('stocks', 'reports.stock_id', '=', 'stocks.id');
                        break;
                    case 'title':
                        $query = $this->whereLikeTitle($query, $search['value']);
                        $queryTotal = $this->whereLikeTitle($queryTotal, $search['value']);
                        break;
                }
            }

            $query = $this->whereDate($query, $search);
            $queryTotal = $this->whereDate($queryTotal, $search);
        }

        return [
            'data' => $query->offset($data['start'])
                ->limit($data['limit'])
                ->orderByDesc('date')
                ->get(),
            'total' => $queryTotal->count(),
        ];
    }

    /**
     * @param int $id
     *
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(int $id)
    {
        return Report::query()
            ->join('stocks', 'reports.stock_id', '=', 'stocks.id')
            ->where(DB::raw('`reports`.`id`'), $id)
            ->first();
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data)
    {
        return Report::query()->insert($data);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return (bool)Report::query()->where('id', $id)->update($data);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return (bool)Report::query()->where('id', $id)->delete();
    }

    /**
     * @param Builder $query
     * @param $value
     *
     * @return Builder
     */
    private function whereLikeTitle(Builder $query, $value)
    {
        return $query->where('title', 'like', "%{$value}%");
    }

    /**
     * @param Builder $query
     * @param $value
     *
     * @return Builder
     */
    private function whereCode(Builder $query, $value)
    {
        return $query->where(DB::raw('`stocks`.`code`'), $value);
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereDate(Builder $query, array $data)
    {
        if (! is_null($data['start_date']) && ! is_null($data['end_date'])) {
            return $query->whereBetween('date', [$data['start_date'], $data['end_date']]);
        } elseif (! is_null($data['start_date'])) {
            return $query->where('date', '<=', "{$data['start_date']} 23:59:59");
        } elseif (! is_null($data['end_date'])) {
            return $query->where('date', '>=', "{$data['end_date']} 00:00:00");
        }

        return $query;
    }
}
