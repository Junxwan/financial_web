<?php

namespace App\Services;

use App\Models\Stock\Price;
use App\Models\Cb\Price as CbPrice;
use Illuminate\Support\Facades\DB;

class Observe
{
    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function cbPriceVolumes(string $code)
    {
        $price = CbPrice::query()
            ->select('date', 'close', DB::RAW("round(increase, 2) as increase"), 'volume')
            ->join('cbs', 'cb_prices.cb_id', '=', 'cbs.id')
            ->where('cbs.code', $code)
            ->orderBy('date')
            ->get();

        return $price->map(function ($v, $i) use ($price) {
            $v['ok'] = false;

            if ($i > 10) {
                $result = $this->getCbPriceVolume($price, $v, $i);

                if (! is_null($result)) {
                    $v['ok'] = true;
                    $v['avg_5_volume'] = $result['avg_5_volume'];
                    $v['avg_10_volume'] = $result['avg_10_volume'];
                    $v['avg_5_close'] = $result['avg_5_close'];
                    $v['avg_10_close'] = $result['avg_10_close'];
                    $v['weak_increase'] = $result['weak_increase'];
                }
            }

            return $v;
        });
    }

    /**
     * @param string $date
     *
     * @return array
     */
    public function cbPriceVolumeByDate(string $date)
    {
        $endDate = Price::query()
            ->select('date')
            ->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->where('code', 'OTC')
            ->orderByDesc('date')
            ->limit(11)
            ->get()
            ->last()
            ->date;

        $prices = CbPrice::query()
            ->select('name', 'code', 'date', 'close', DB::RAW("round(increase, 2) as increase"), 'volume')
            ->join('cbs', 'cb_prices.cb_id', '=', 'cbs.id')
            ->whereBetween('date', [$endDate, $date])
            ->orderBy('date')
            ->get()
            ->groupBy('code');

        $data = [];
        foreach ($prices as $code => $values) {
            if ($values->count() > 10) {
                $result = $this->getCbPriceVolume($values, $v = $values->last(), 11);

                if (is_null($result)) {
                    continue;
                }

                $data[] = array_merge([
                    'code' => $code,
                    'name' => $v->name,
                    'close' => $v->close,
                    'increase' => $v->increase,
                    'volume' => $v->volume,
                ], $result);
            }
        }

        return $data;
    }

    /**
     * @param $price
     * @param $value
     * @param $index
     *
     * @return array|null
     */
    private function getCbPriceVolume($price, $value, $index)
    {
        $data5 = $price->slice($index - 4, 5);
        $data10 = $price->slice($index - 9, 10);

        $avg5Volume = round($data5->avg('volume'));

        // 市價在140以下
        if ($value->close >= 140 || $avg5Volume < 50) {
            return null;
        }

        // 5日均量>50
        if ($avg5Volume < 50) {
            return null;
        }

        $avg10Volume = round($data10->avg('volume'));

        // 5日均量>10日均量
        if ($avg5Volume < $avg10Volume) {
            return null;
        }

        $avg5Close = round($data5->avg('close'), 2);
        $avg10Close = round($data10->avg('close'), 2);

        // 5日均價>10日均價
        if ($avg5Close < $avg10Close) {
            return null;
        }

        $weakIncrease = round((($value->close / $price->slice($index - 4, 1)->first()->close) - 1) * 100, 2);

        // 週漲幅達2%
        if ($weakIncrease < 2) {
            return null;
        }

        return [
            'avg_5_volume' => $avg5Volume,
            'avg_10_volume' => $avg10Volume,
            'avg_5_close' => $avg5Close,
            'avg_10_close' => $avg10Close,
            'weak_increase' => $weakIncrease,
        ];
    }
}
