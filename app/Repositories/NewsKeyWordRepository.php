<?php

namespace App\Repositories;

use App\Models\NewsKeyWord;

class NewsKeyWordRepository extends Repository
{
    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(array $data)
    {
        $query = NewsKeyWord::query();

        return [
            'data' => $query->offset($data['start'])
                ->limit($data['limit'])
                ->get(),
            'total' => $query->count(),
        ];
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function create(array $data)
    {
        return NewsKeyWord::query()->insert([
            'name' => $data['name'],
            'keys' => $data['keys'],
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
        return NewsKeyWord::query()->where('id', $id)->update([
            'name' => $data['name'],
            'keys' => $data['keys'],
        ]);
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function delete(int $id)
    {
        return NewsKeyWord::query()->where('id', $id)->delete();
    }
}
