<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Cb;

class IndexRepository
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Cb::query()->select(
            'cbs.id', 'cbs.code', 'cbs.name', 'start_date', 'end_date',
            'publish_total_amount', 'conversion_price', 'start_conversion_date', 'conversion_premium_rate',
            'is_collateral', 'url'
        );

        $order = 'start_date';

        if (isset($data['search']) && ! is_null($search = $data['search'])) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $query->where('code', 'LIKE', "{$search['value']}%");
            }

            if (isset($search['name']) && ! empty($search['name'])) {
                $order = $search['name'];
            }
        }

        return [
            'total' => $query->count(),
            'data' => $query->offset($data['start'])
                ->limit($data['limit'])
                ->orderByDesc($order)
                ->get(),
        ];
    }
}
