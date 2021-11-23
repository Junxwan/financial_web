<?php

namespace App\Repositories;

use App\Models\News\KeyWord;

class NewsKeyWordRepository extends Repository
{
    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(array $data)
    {
        $query = KeyWord::query();

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
        return KeyWord::query()->insert([
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
        return KeyWord::query()->where('id', $id)->update([
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
        return KeyWord::query()->where('id', $id)->delete();
    }
}
