<?php

namespace App\Repositories;

use App\Models\Stock\Population;

class PopulationRepository
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Population::query()->select('id', 'name');

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
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data)
    {
        return Population::query()->insert(['name' => $data['name']]);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return Population::query()->where('id', $id)->update([
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
        return (bool)Population::query()->where('id', $id)->delete();
    }
}
