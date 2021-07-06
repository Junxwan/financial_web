<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Models\StockTag;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StockRepository extends Repository
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Stock::query()->select('id', 'code', 'name', 'classification_id', 'market', DB::RAW('"tags"'))
            ->whereIn('market', [1, 2]);
        $queryTotal = Stock::query()->whereIn('market', [1, 2])->getQuery();

        if (isset($data['search']) && ! is_null($search = $data['search'])) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
                $queryTotal = $this->whereLike($queryTotal, $search);
            }
        }

        $data = $query->offset($data['start'])
            ->limit($data['limit'])
            ->orderBy('code')
            ->get();

        $tags = StockTag::query()->select(
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
            'total' => $queryTotal->count(),
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
            foreach ($data['tags'] as $v) {
                $insert[] = [
                    'stock_id' => $id,
                    'tag_id' => $v,
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
            foreach ($data['tags'] as $v) {
                $insert[] = [
                    'stock_id' => $id,
                    'tag_id' => $v,
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
}
