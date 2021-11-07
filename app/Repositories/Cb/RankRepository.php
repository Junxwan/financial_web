<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Balance;
use App\Models\Cb\Cb;
use App\Models\Cb\ConversionPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RankRepository
{
    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function list(array $arg)
    {
        $cb = Cb::query()->select(
            'cbs.id', 'cbs.stock_id', 'cbs.code', 'cbs.name', 'cbs.start_date', 'cbs.end_date',
            DB::RAW('round(cb_prices.increase,2) as increase'), 'cbs.publish_total_amount',
            'cb_prices.date', 'cb_prices.close as cb_close', 'prices.close'
        )->join('cb_prices', 'cbs.id', '=', 'cb_prices.cb_id')
            ->join('prices', 'cbs.stock_id', '=', 'prices.stock_id')
            ->where('cbs.start_date', '<=', $arg['date'])
            ->where('cbs.end_date', '>=', $arg['date'])
            ->where('cb_prices.date', $arg['date'])
            ->where('prices.date', $arg['date'])
            ->get();

        $date = Carbon::parse($arg['date']);

        $balance = Balance::query()
            ->select('cb_id', 'balance')
            ->where('year', $date->year)
            ->where('month', ($date->month - 1) == 0 ? 12 : $date->month - 1)
            ->get();

        $conversionPrice = ConversionPrice::query()
            ->select('cb_id', DB::RAW('min(value) as value'), DB::RAW('max(stock) as stock'))
            ->whereIn('cb_id', $cb->pluck('id'))
            ->where('date', '<=', $arg['date'])
            ->groupBy('cb_id')
            ->get();

        $data = $cb->map(function ($value) use ($balance, $conversionPrice) {
            $value['conversion_price'] = '';
            $value['off_price'] = '';
            $value['premium'] = '';
            $value['balance_rate'] = '';

            if (! is_null($v = $conversionPrice->where('cb_id', $value->id)->first())) {
                $value['conversion_price'] = $v->value;
                $value['off_price'] = round(($value->close / $v->value) * 100, 2);
                $value['premium'] = round((($value->cb_close - $value['off_price']) / $value['off_price']) * 100, 2);
            }

            if (! is_null($b = $balance->where('cb_id', $value->id)->first())) {
                if ($b->balance == 0) {
                    $value['balance_rate'] = 0;
                } else {
                    $value['balance_rate'] = round((($b->balance * 100000) / $value->publish_total_amount) * 100);
                }
            }

            return $value;
        });

        if (isset($arg['order']) && $arg['order'] != '') {
            $order = explode('-', $arg['order']);

            if ($order[1] == 'desc') {
                $data = $data->sortByDesc($order[0])->values();
            } else {
                $data = $data->sortBy($order[0])->values();
            }
        }

        return $data;
    }
}
