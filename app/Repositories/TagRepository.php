<?php

namespace App\Repositories;

use App\Models\Classification;
use App\Models\Stock;
use App\Models\StockTag;
use App\Models\Tag;
use App\Models\TagExponent;
use Illuminate\Database\Query\Builder;

class TagRepository extends Repository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Tag::query()->get();
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(int $id)
    {
        return Tag::query()->where('id', $id)->first();
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
        $query = Tag::query()->select(
            'tags.id', 'name', 'tag_exponents.stock_id'
        )->leftJoin('tag_exponents', 'tag_exponents.tag_id', '=', 'tags.id');
        $queryTotal = Tag::query();

        if (isset($data['search']) && ! is_null($search = $data['search'])) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
                $queryTotal = $this->whereLike($queryTotal->getQuery(), $search);
            }
        }

        return [
            'data' => $query->offset($data['start'])
                ->limit($data['limit'])
                ->get(),
            'total' => $queryTotal->count(),
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
        return $this->transaction(function () use ($data) {
            $tagId = Tag::query()->insertGetId([
                'name' => $data['name'],
            ]);

            if (! $data['isExponent']) {
                return $tagId > 0;
            }

            return $this->insertTagExponent($tagId, $data['name']);
        });
    }

    /**
     * @param int $tagId
     * @param string $name
     *
     * @return bool
     */
    private function insertTagExponent(int $tagId, string $name)
    {
        $id = str_pad(TagExponent::query()->count(), 3, "0", STR_PAD_LEFT);
        return TagExponent::query()->insert([
            'stock_id' => Stock::query()->insertGetId([
                'code' => "TE{$id}",
                'name' => $name,
                'classification_id' => Classification::query()->where('name', '產業指數')->first()->id,
            ]),
            'tag_id' => $tagId,
        ]);
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
            $model = Tag::query()->where('id', $id)->first();

            if (is_null($model)) {
                return false;
            }

            $model->name = $data['name'];

            if (! $model->save()) {
                return false;
            }

            if ($data['isExponent']) {
                $model = TagExponent::query()->where('tag_id', $id)->first();

                if (is_null($model)) {
                    return $this->insertTagExponent($id, $data['name']);
                }
            }

            return true;
        });
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return (bool)Tag::query()->where('id', $id)->delete();
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereLike(Builder $query, array $data)
    {
        return $query->where('tags.name', 'like', "%{$data['value']}%");
    }
}
