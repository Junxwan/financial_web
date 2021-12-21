<?php

namespace App\Repositories;

use App\Models\Stock\Populations;

class PopulationRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Populations::query()->get();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Populations::query()->select('id', 'name');

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
        return Populations::query()->insert(['name' => $data['name']]);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return Populations::query()->where('id', $id)->update([
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
        return (bool)Populations::query()->where('id', $id)->delete();
    }
}
