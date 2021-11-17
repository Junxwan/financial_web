<?php

namespace App\Repositories;

use App\Models\Stock\Price;
use Illuminate\Support\Facades\DB;

class CategoryRepository extends Repository
{
    /**
     * @param string $code
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(string $code, array $data)
    {
        $query = Price::query()
            ->select(
                DB::RAW('stocks.code'),
                DB::RAW('stocks.name'),
                DB::RAW('prices.date'),
                DB::RAW('prices.increase'),
                DB::RAW('prices.volume'),
                DB::RAW('ROUND(prices.volume_ratio, 2) AS volume_ratio'),
            )
            ->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->where('stocks.code', 'like', "{$code}%");

        if (isset($data['search'])) {
            $search = $data['search'];
            if (isset($search['start_date']) && ! is_null($search['start_date'])) {
                $query = $query->where('date', $search['start_date']);
            } else {
                $query = $query->where('date', function ($query) {
                    $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
                });
            }
        }

        return $query->orderByDesc(isset($data['order']) ? $data['order'] : 'increase')
            ->get()
            ->whereNotIn('code', $code)
            ->values();
    }
}
