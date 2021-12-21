<?php

namespace App\Repositories;

use App\Models\Stock\Price;
use App\Models\Stock\Tag;
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
            'stocks.id', 'stocks.code', 'stocks.name', 'prices.date',
            'prices.close', 'prices.fund_value', 'prices.foreign_value',
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
        } else {
            $query = $this->latestDate($query);
            $queryTotal = $this->latestDate($queryTotal);
        }

        $total = $queryTotal->count();
        $data = $query
            ->offset($data['start'])
            ->limit($data['limit'])
            ->orderByDesc(isset($data['order']) ? $data['order'] : 'increase')
            ->get();

        $tags = Tag::query()->select(
            DB::RAW('stock_tags.stock_id'),
            DB::RAW('tags.id'),
            DB::RAW('tags.name')
        )->join('tags', 'tags.id', '=', 'stock_tags.tag_id')
            ->whereIn('stock_tags.stock_id', $data->pluck('id'))
            ->get();

        return [
            'data' => $data->map(function ($value) use ($tags) {
                $t = [];
                foreach ($tags as $v) {
                    if ($value->id == $v->stock_id) {
                        $t[] = [
                            'id' => $v->id,
                            'name' => $v->name,
                        ];
                    }
                }

                $value->tags = $t;
                return $value;
            }),
            'total' => $total,
        ];
    }

    /**
     * @param array $ids
     * @param string $date
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function yesterdayListById(array $ids, string $date)
    {
        return Price::query()
            ->whereIn('stock_id', $ids)
            ->where('date', function ($query) use ($date) {
                $query->select('date')->from('prices')->where('date', '<', $date)->orderByDesc('id')->limit(1);
            })->get();
    }

    /**
     * @param array $ids
     * @param null $date
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByPopulation(array $ids, $date = null)
    {
        $query = Price::query()->select(
            'prices.stock_id', 'increase', 'value',
            DB::RAW('GROUP_CONCAT(stock_populations.population_id) as population_id'))
            ->join('stock_populations', 'stock_populations.stock_id', '=', 'prices.stock_id')
            ->whereIn('stock_populations.population_id', $ids);

        if (is_null($date)) {
            $query = $query->where('date', function ($query) {
                $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
            });
        } else {
            $query = $query->where('date', $date);
        }

        return $query->groupBy(['stock_id'])->get()->map(function ($value) {
            $value->population_id = explode(',', $value->population_id);
            return $value;
        });
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function population(int $id, int $year)
    {
        $year -= 1;
        $m = date('m');
        $d = date('d');
        return Price::query()->select(
            'open', 'close', DB::RAW('ROUND(increase, 2) AS increase'), 'volume', 'date', 'high', 'low'
        )->join('populations', 'populations.stock_id', '=', 'prices.stock_id')
            ->where('populations.id', $id)
            ->where('prices.date', '>=', "{$year}-{$m}-{$d}")
            ->orderBy('prices.date')
            ->get();
    }

    /**
     * @param int $tag
     * @param int $year
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function stockByTag(int $tag, int $year)
    {
        $year -= 1;
        $m = date('m');
        $d = date('d');
        return Price::query()->select(
            'prices.stock_id', 'open', 'close', DB::RAW('ROUND(increase, 2) AS increase'), 'volume', 'date', 'high',
            'low'
        )->join('stock_tags', 'stock_tags.stock_id', '=', 'prices.stock_id')
            ->where('stock_tags.tag_id', $tag)
            ->where('prices.date', '>=', "{$year}-{$m}-{$d}")
            ->orderBy('prices.date')
            ->get()
            ->groupBy('stock_id');
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function stock(string $code, int $year)
    {
        $year -= 1;
        $m = date('m');
        $d = date('d');
        return Price::query()->select(
            'open', 'close', DB::RAW('ROUND(increase, 2) AS increase'), 'volume', 'date', 'high',
            'low'
        )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->where('stocks.code', $code)
            ->where('prices.date', '>=', "{$year}-{$m}-{$d}")
            ->orderBy('prices.date')
            ->get();
    }

    /**
     * @param string $code
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function month(string $code)
    {
        return Price::query()
            ->select(
                DB::RAW('YEAR(date) as year'),
                DB::RAW('MONTH(date) as month'),
                DB::RAW('MAX(date) as date'),
                'close'
            )->join('stocks', 'prices.stock_id', '=', 'stocks.id')
            ->where('code', $code)
            ->groupBy([DB::RAW('YEAR(date)'), DB::RAW('MONTH(date)')])
            ->orderByDesc('year')
            ->orderByDesc('month')
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
