<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Cb;
use App\Models\Cb\Price as CbPrice;
use App\Models\Price;

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
            'cbs.id', 'cbs.stock_id', 'cbs.code', 'cbs.name', 'start_date', 'end_date',
            'publish_total_amount', 'conversion_price', 'conversion_stock', 'start_conversion_date',
            'conversion_premium_rate',
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

        $data = $query->offset($data['start'])
            ->limit($data['limit'])
            ->orderByDesc($order)
            ->get();

        $date = CbPrice::query()
            ->select('date')
            ->orderByDesc('date')
            ->limit(1)
            ->first()->date;

        $cbPrice = CbPrice::query()->select('cb_id', 'close')
            ->where('date', $date)
            ->whereIn('cb_id', $data->where('conversion_price', '!=', 0)->pluck('id'))
            ->get();

        $price = Price::query()->select('stock_id', 'close')
            ->where('date', $date)
            ->whereIn('stock_id', $data->where('conversion_price', '!=', 0)->pluck('stock_id'))
            ->get();

        return [
            'total' => Cb::query()->count(),
            'data' => $data->map(function ($value) use ($cbPrice, $price) {
                $value['premium'] = 0;
                $value['off_price'] = 0;
                $value['price'] = 0;
                $value['s_price'] = 0;

                if (! is_null($cbP = $cbPrice->where('cb_id', $value->id)->first())) {
                    $value['price'] = $cbP->close;

                    if (! is_null($p = $price->where('stock_id', $value->stock_id)->first())) {
                        $value['s_price'] = $p->close;

                        $value['off_price'] = round($p->close * ($value->conversion_stock / 1000), 2);
                        $value['premium'] = round((($cbP->close - $value['off_price']) / $value['off_price']) * 100, 2);
                    }
                }

                return $value;
            }),
        ];
    }
}
