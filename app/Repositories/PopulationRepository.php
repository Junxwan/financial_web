<?php

namespace App\Repositories;

use App\Models\Stock\Classification;
use App\Models\Stock\Populations;
use App\Models\Stock\Stock;

class PopulationRepository extends Repository
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
        return $this->transaction(function () use ($data) {
            $id = Stock::query()->insertGetId([
                'code' => random_int(100000, 999999),
                'name' => $data['name'],
                'classification_id' => Classification::query()->where('name', '族群')->first()->id,
            ]);

            return Populations::query()->insert([
                'name' => $data['name'],
                'stock_id' => $id,
            ]);
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
