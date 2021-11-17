<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Balance;
use App\Models\Cb\Cb;
use App\Models\Cb\Price as CbPrice;
use App\Models\Stock\Price;

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
            'publish_total_amount', 'conversion_price', 'conversion_stock',
            'is_collateral', 'url'
        );

        if (isset($data['search']) && ! is_null($search = $data['search'])) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $query->where('code', 'LIKE', "{$search['value']}%");
            }

            if (isset($search['start_date'])) {
                $date = $search['start_date'];
            } else {
                $date = CbPrice::query()
                    ->select('date')
                    ->orderByDesc('date')
                    ->limit(1)
                    ->first()->date;
            }
        } else {
            $date = CbPrice::query()
                ->select('date')
                ->orderByDesc('date')
                ->limit(1)
                ->first()->date;
        }

        $data = $query->offset($data['start'])
            ->limit($data['limit'])
            ->orderByDesc('start_date')
            ->get();

        $cbPrice = CbPrice::query()->select('cb_id', 'close')
            ->where('date', $date)
            ->whereIn('cb_id', $data->where('conversion_price', '!=', 0)->pluck('id'))
            ->get();

        $price = Price::query()->select('stock_id', 'close')
            ->where('date', $date)
            ->whereIn('stock_id', $data->where('conversion_price', '!=', 0)->pluck('stock_id'))
            ->get();

        $balance = Balance::query()
            ->select('cb_id', 'balance')
            ->whereIn('cb_id', $data->pluck('id'))
            ->where('year', date('Y'))
            ->where('month', (int)date('m') - 1)
            ->get();

        return [
            'total' => Cb::query()->count(),
            'data' => $data->map(function ($value) use ($cbPrice, $price, $balance) {
                $value['premium'] = 0;
                $value['off_price'] = 0;
                $value['price'] = 0;
                $value['s_price'] = 0;

                if (! is_null($cbP = $cbPrice->where('cb_id', $value->id)->first())) {
                    $value['price'] = $cbP->close;

                    if (! is_null($p = $price->where('stock_id', $value->stock_id)->first())) {
                        $value['s_price'] = $p->close;

                        if ($value->conversion_stock > 0) {
                            $value['off_price'] = round(($p->close / $value->conversion_price) * 100, 2);
                            $value['premium'] = round((($cbP->close - $value['off_price']) / $value['off_price']) * 100,
                                2);
                        }
                    }
                }

                if (! is_null($b = $balance->where('cb_id', $value->id)->first())) {
                    $value['balance_rate'] = round((($b->balance * 100000) / $value->publish_total_amount) * 100);
                } else {
                    $value['balance_rate'] = '';
                }

                return $value;
            }),
        ];
    }
}
