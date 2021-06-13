<?php

namespace App\Services;

use App\Models\Cash as Model;
use Illuminate\Support\Facades\DB;

class Cash
{
    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function recent(string $code, int $year, int $quarterly)
    {
        $cash = Model::query()->select(
            DB::RAW('cashs.year'),
            DB::RAW('cashs.quarterly'),
            DB::RAW('cashs.real_estate'),
            DB::RAW('cashs.depreciation'),
        )->join('stocks', 'stocks.id', '=', 'cashs.stock_id')
            ->where('stocks.code', $code)
            ->where('cashs.year', '<=', $year)
            ->orderByDesc('cashs.year')
            ->orderByDesc('cashs.quarterly')
            ->limit(24)
            ->get()->filter(function ($v) use ($year, $quarterly) {
                return ($year == $v->year) ? $v->quarterly <= $quarterly : true;
            });

        return q4r($cash, [
            'real_estate',
            'depreciation',
        ], true);
    }
}
