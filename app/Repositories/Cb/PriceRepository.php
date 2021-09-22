<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Price;
use Illuminate\Support\Facades\DB;

class PriceRepository
{
    /**
     * @param string $code
     */
    public function get(string $code)
    {
        return Price::query()->select(
            'open', 'close', DB::RAW('ROUND(increase, 2) AS increase'), 'volume', 'date', 'high', 'low'
        )->join('cbs', 'cbs.id', 'cb_prices.cb_id')
            ->where('cbs.code', $code)
            ->orderBy('cb_prices.date')
            ->get();
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function month(string $code)
    {
        $year = (int)date('Y');
        $month = (int)date('m');

        return Price::query()->select(
            'year', 'month', 'close'
        )->join('cbs', 'cbs.id', 'cb_prices.cb_id')
            ->whereIn('date', function ($query) use ($code) {
                $query->select(DB::RAW('MAX(date) AS date'))
                    ->from('cb_prices')
                    ->join('cbs', 'cbs.id', 'cb_prices.cb_id')
                    ->where('cbs.code', $code)
                    ->groupBy(['year', 'month']);
            })->where('cbs.code', $code)
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->filter(function ($value) use ($year, $month) {
                return ! ($value->year == $year && $value->month == $month);
            });
    }
}
