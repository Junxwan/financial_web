<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Query\Builder;

class TagRepository extends Repository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Tag::query()->select('id', 'name')->get();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Tag::query()->select('id', 'name');
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
     * @param string $name
     *
     * @return bool
     */
    public function insert(string $name)
    {
        return Tag::query()->insert([
            'name' => $name,
        ]);
    }

    /**
     * @param int $id
     * @param string $name
     *
     * @return bool
     */
    public function update(int $id, string $name)
    {
        return (bool)Tag::query()->where('id', $id)->update([
            'name' => $name,
        ]);
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
        return $query->where('name', 'like', "%{$data['value']}%");
    }
}
