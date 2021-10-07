<?php

namespace App\Repositories;

use App\Models\Price;
use App\Models\Stock;
use App\Models\StockTag;
use App\Models\Tag;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StockRepository extends Repository
{
    /**
     * @param array $ids
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function gets(array $ids)
    {
        return Stock::query()->whereIn('id', $ids)->orderBy('code')->get();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Stock::query()->select(
            'stocks.id',
            'stocks.code',
            'stocks.name',
            'stocks.classification_id',
            'stocks.market',
            DB::RAW('"tags"')
        )->whereIn('market', [1, 2]);

        if (isset($data['search']) && ! is_null($search = $data['search'])) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
            }

            if (isset($search['name']) && ! empty($search['name'])) {
                $query = $query->join('stock_tags', 'stock_tags.stock_id', '=', 'stocks.id')
                    ->where('stock_tags.tag_id', $search['name']);
            }
        }

        $data = $query->offset($data['start'])
            ->limit($data['limit'])
            ->orderBy('stocks.code')
            ->get();

        $tags = StockTag::query()->select(
            DB::RAW('stock_tags.stock_id'),
            DB::RAW('stock_tags.isGroup'),
            DB::RAW('stock_tags.order'),
            DB::RAW('tags.id'),
            DB::RAW('tags.name')
        )->join('tags', 'tags.id', '=', 'stock_tags.tag_id')
            ->whereIn('stock_tags.stock_id', $data->pluck('id'))
            ->orderBy('stock_tags.order')
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
            'total' => $query->count(),
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function priceList(array $data)
    {
        if (! is_null($search = $data['search']) && isset($search['value']) && ! empty($search['value'])) {
            $queryTotal = Price::query()->join('stocks', 'stocks.id', '=', 'prices.stock_id');
            $query = Price::query()->select(
                'stocks.code', 'stocks.name', 'prices.date', 'prices.close', 'prices.fund_value',
                'prices.foreign_value',
                DB::RAW('ROUND(prices.increase, 2) AS increase'), 'prices.volume', 'prices.value', 'stocks.market',
                DB::RAW('classifications.name AS cName'), DB::RAW('ROUND(prices.increase_5,2) AS increase_5'),
                DB::RAW('ROUND(prices.increase_23,2) AS increase_23'),
                DB::RAW('ROUND(prices.increase_63,2) AS increase_63')
            )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
                ->join('classifications', 'classifications.id', '=', 'stocks.classification_id');

            if (isset($search['start_date']) && ! is_null($search['start_date'])) {
                $query = $query->where('date', '<=', $search['start_date']);
                $queryTotal = $queryTotal->where('date', '<=', $search['start_date']);
            } else {
                $query = $this->latestDateByPriceList($query->getQuery());
                $queryTotal = $this->latestDateByPriceList($queryTotal->getQuery());
            }

            $query = $this->whereLike($query, $search);
            $queryTotal = $this->whereLike($queryTotal, $search);

            $total = $queryTotal->count();

            $data = $query
                ->offset($data['start'])
                ->limit($data['limit'])
                ->orderByDesc(isset($data['order']) ? $data['order'] : 'prices.date')
                ->get();
        } else {
            $total = 0;
            $data = [];
        }

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function search(string $code)
    {
        return Stock::query()
            ->select(
                DB::Raw('stocks.id AS id'),
                'name',
                'capital',
                DB::Raw('equities.start_stock AS start_capital'),
                'year',
                'quarterly'
            )
            ->leftJoin('equities', 'equities.stock_id', '=', 'stocks.id')
            ->where('code', $code)
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->first();
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function create(array $data)
    {
        return $this->transaction(function () use ($data) {
            if (! $id = Stock::insertGetId([
                'code' => $data['code'],
                'name' => $data['name'],
                'classification_id' => $data['classification_id'],
                'market' => $data['market'],
            ])) {
                return false;
            }

            $insert = [];
            foreach ($data['tags'] as $i => $v) {
                $insert[] = [
                    'stock_id' => $id,
                    'tag_id' => $v,
                    'order' => $i,
                ];
            }

            return StockTag::insert($insert);
        });
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            Stock::query()->where('id', $id)->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'classification_id' => $data['classification_id'],
                'market' => $data['market'],
            ]);

            StockTag::query()->where('stock_id', $id)->delete();

            $insert = [];
            foreach ($data['tags'] as $i => $v) {
                $insert[] = [
                    'stock_id' => $id,
                    'tag_id' => $v,
                    'order' => $i,
                ];
            }

            return StockTag::insert($insert);
        });
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return $this->transaction(function () use ($id) {
            return Stock::query()->where('id', $id)->delete() &&
                StockTag::query()->where('stock_id', $id)->delete();
        });
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereLike(Builder $query, array $data)
    {
        return $query->where('code', 'like', "{$data['value']}%")
            ->orWhere('name', 'like', "%{$data['value']}%");
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    private function latestDateByPriceList(Builder $query)
    {
        return $query->where('prices.date', '<=', function ($query) {
            $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
        });
    }

    /**
     * @param string $code
     */
    public function name(string $code)
    {
        $stocks = Stock::query()
            ->select(
                'stocks.id', 'stocks.code', 'stocks.name',
                DB::RAW('classifications.name as c_name'), 'stocks.capital',
            )
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id')
            ->where('code', $code)
            ->first();

        $stocks->tags = StockTag::query()
            ->select('tags.name')
            ->join('tags', 'tags.id', '=', 'stock_tags.tag_id')
            ->where('stock_id', $stocks->id)
            ->orderBy('stock_tags.order')
            ->get()
            ->pluck('name');

        return $stocks;
    }

    /**
     * @param int $tag
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function namesByTag(int $tag)
    {
        $stocks = StockTag::query()
            ->select(
                'stocks.id', 'stocks.code', 'stocks.name',
                DB::RAW('classifications.name as c_name'), 'stocks.capital',
            )
            ->join('stocks', 'stocks.id', '=', 'stock_tags.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id')
            ->where('stock_tags.tag_id', $tag)
            ->get();

        $tags = StockTag::query()
            ->select('stock_tags.stock_id', 'tags.name')
            ->join('tags', 'tags.id', '=', 'stock_tags.tag_id')
            ->whereIn('stock_id', $stocks->pluck('id'))
            ->orderBy('stock_tags.order')
            ->get()
            ->groupBy('stock_id');

        return $stocks->map(function ($value) use ($tags) {
            $value->tags = $tags[$value->id]->pluck('name');
            return $value;
        });
    }
}
