<?php

namespace App\Repositories;

use App\Models\Stock\Tag as StockTag;
use App\Models\Stock\Tags;
use App\Models\Stock\TagExponent;
use Illuminate\Support\Facades\DB;

class TagRepository extends Repository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Tags::query()->get();
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(int $id)
    {
        return Tags::query()->where('id', $id)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function exponents()
    {
        return TagExponent::query()
            ->select('tags.id', 'tags.name', 'stocks.code')
            ->join('tags', 'tags.id', '=', 'tag_exponents.tag_id')
            ->join('stocks', 'stocks.id', '=', 'tag_exponents.stock_id')
            ->get();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Tags::query()->select('id', 'name');

        if (isset($data['search']) && ! is_null($search = $data['search'])) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $query->where('name', 'like', "%{$data['value']}%");
            }
        }

        $total = $query->count();

        return [
            'data' => $query->offset($data['start'])
                ->limit($data['limit'])
                ->get(),
            'total' => $total,
        ];
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function stockByTag(int $id)
    {
        return StockTag::query()
            ->select('stocks.code', 'stocks.name')
            ->join('stocks', 'stock_tags.stock_id', '=', 'stocks.id')
            ->where('stock_tags.tag_id', $id)
            ->orderBy('code')
            ->get();
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data)
    {
        return Tags::query()->insert(['name' => $data['name']]);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return Tags::query()->where('id', $id)->update([
            'name' => $data['name'],
        ]);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return (bool)Tags::query()->where('id', $id)->delete();
    }
}
