<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Cb;
use App\Models\Cb\ConversionPrice;
use App\Models\Cb\Price as CbPrice;
use Illuminate\Support\Facades\DB;

class PriceRepository
{
    /**
     * @param string $code
     */
    public function get(string $code)
    {
        return CbPrice::query()->select(
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

        return CbPrice::query()->select(
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

    /**
     * @param string $code
     *
     * @return array
     */
    public function premium(string $code)
    {
        $cb = Cb::query()
            ->select('id', 'name')
            ->where('code', $code)
            ->first();

        $conversionPrice = ConversionPrice::query()
            ->select('stock', 'date')
            ->where('cb_id', $cb->id)
            ->orderByDesc('date')
            ->get();

        $price = CbPrice::query()->select(
            'cb_prices.date',
            'prices.close',
            DB::RAW('cb_prices.close as cb_close'),
        )->join('cbs', 'cbs.id', '=', 'cb_prices.cb_id')
            ->join('prices', function ($query) {
                $query->on('cbs.stock_id', '=', 'prices.stock_id')
                    ->where(DB::RAW('cb_prices.date'), '=', DB::RAW('prices.date'));
            })->where('cbs.code', $code)
            ->orderByDesc('cb_prices.date')
            ->get();

        return [
            'data' => $price->map(function ($value) use ($conversionPrice) {
                $offPrice = round(($value->close / $conversionPrice->where('date', '<=',
                            $value->date)->first()->value) * 100, 2);
                $value['premium'] = round((($value->cb_close - $offPrice) / $offPrice) * 100, 2);
                $value['off_price'] = $offPrice;
                return $value;
            }),
            'name' => $cb->name,
            'conversion_prices' => ConversionPrice::query()
                ->select('date', 'value')
                ->where('cb_id', $cb->id)
                ->orderBy('date')
                ->get(),
        ];
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function conversion(string $code)
    {
        return ConversionPrice::query()
            ->select('cb_conversion_prices.date', 'cb_conversion_prices.value')
            ->join('cbs', 'cbs.id', '=', 'cb_conversion_prices.cb_id')
            ->where('cbs.code', $code)
            ->orderBy('cb_conversion_prices.date')
            ->get();
    }
}
