<?php

namespace App\Repositories;

use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PriceRepository extends Repository
{
    /**
     * @var int[]
     */
    private $market = [
        'TSE' => 1,
        'OTC' => 2,
    ];

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $queryTotal = Price::query()->join('stocks', 'stocks.id', '=', 'prices.stock_id');
        $query = Price::query()->select(
            'stocks.code', 'stocks.name', 'prices.open', 'prices.close', 'prices.fund_value', 'prices.foreign_value',
            DB::RAW('ROUND(prices.increase, 2) AS increase'), 'prices.volume', 'prices.value', 'stocks.market',
            DB::RAW('classifications.name AS cName'), DB::RAW('ROUND(prices.increase_5,2) AS increase_5'),
            DB::RAW('ROUND(prices.increase_23,2) AS increase_23'), DB::RAW('ROUND(prices.increase_63,2) AS increase_63')
        )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id')
            ->whereIn('stocks.market', [1, 2]);

        if (isset($data['search'])) {
            $search = $data['search'];
            if (isset($search['start_date']) && ! is_null($search['start_date'])) {
                $query = $query->where('date', $search['start_date']);
                $queryTotal = $queryTotal->where('date', $search['start_date']);
            } else {
                $query = $this->latestDate($query);
                $queryTotal = $this->latestDate($queryTotal);
            }

            if (isset($search['name']) && ! is_null($search['name'])) {
                $query = $query->where('stocks.market', $this->market[$search['name']]);
                $queryTotal = $queryTotal->where('stocks.market', $this->market[$search['name']]);
            }
        }

        $total = $queryTotal->count();

        return [
            'data' => $query
                ->offset($data['start'])
                ->limit($data['limit'])
                ->orderByDesc(isset($data['order']) ? $data['order'] : 'increase')
                ->get(),
            'total' => $total,
        ];
    }

    /**
     * @param array $tags
     * @param null $date
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByTag(array $tags, $date = null)
    {
        $query = Price::query()->select(
            'prices.stock_id', 'increase', 'value',
            DB::RAW('GROUP_CONCAT(stock_tags.tag_id) as tag_id'))
            ->join('stock_tags', 'stock_tags.stock_id', '=', 'prices.stock_id')
            ->whereIn('stock_tags.tag_id', $tags);

        if (is_null($date)) {
            $query = $query->where('date', function ($query) {
                $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
            });
        } else {
            $query = $query->where('date', $date);
        }

        return $query->groupBy(['stock_id'])->get()->map(function ($value) {
            $value->tag_id = explode(',', $value->tag_id);
            return $value;
        });
    }

    /**
     * @param int $tag
     * @param int $year
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function exponentByTag(int $tag, int $year)
    {
        $year -= 1;
        $m = date('m');
        $d = date('m');
        return Price::query()->select(
            'open', 'close', DB::RAW('ROUND(increase, 2) AS increase'), 'volume', 'date', 'high', 'low'
        )->join('tag_exponents', 'tag_exponents.stock_id', '=', 'prices.stock_id')
            ->where('tag_exponents.tag_id', $tag)
            ->where('prices.date', '>=', "{$year}-{$m}-{$d}")
            ->orderBy('prices.date')
            ->get();
    }

    /**
     * @param array $codes
     * @param int $year
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function codes(array $codes, int $year)
    {
        //        $now = Carbon::now();
        //        $year -= 1;
        //        return Price::query()->select(
        //            'stock_id', 'open', 'close', 'increase', 'value', 'date'
        //        )->whereIn('stock_id', $codes)->whereBetween('date',
        //            ["{$year}-12-31", $now->format('Y-m-d')]
        //        )->orderByDesc('date')->orderByDesc('stock_id')->get();

        return Price::query()->select(
            'stock_id', 'open', 'close', 'increase', 'value', 'date'
        )->whereIn('stock_id', $codes)
            ->orderBy('date')
            ->orderByDesc('stock_id')
            ->get();
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    private function latestDate(Builder $query)
    {
        return $query->where('date', function ($query) {
            $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
        });
    }
}
