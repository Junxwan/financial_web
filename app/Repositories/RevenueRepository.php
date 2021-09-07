<?php

namespace App\Repositories;

use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class RevenueRepository extends Repository
{
    /**
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(int $year, int $month)
    {
        return Revenue::query()->select(
            'year',
            'month',
            'value',
            DB::RAW("ROUND(qoq, 2) AS qoq"),
            DB::RAW("ROUND(yoy, 2) AS yoy"),
            'stocks.code',
            'stocks.name',
            DB::RAW('classifications.name as cname'),
            'total',
            'y_total',
            'total_increase',
        )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id')
            ->where('year', $year)
            ->where('month', $month)
            ->orderByDesc('yoy')
            ->get();
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function gets(string $code, int $year)
    {
        return Revenue::query()
            ->select(
                DB::RAW('revenues.*')
            )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->where('stocks.code', $code)
            ->where('revenues.year', $year)
            ->get();
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function recent(string $code, int $year, int $month)
    {
        $revenue = Revenue::query()
            ->select(
                DB::RAW('`revenues`.`year`'),
                DB::RAW('`revenues`.`month`'),
                DB::RAW('`revenues`.`value`'),
            )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->where('stocks.code', $code)
            ->where('revenues.year', '<=', $year)
            ->orderByDesc('revenues.year')
            ->orderByDesc('revenues.month')
            ->limit(72)
            ->get()->filter(function ($v) use ($year, $month) {
                return ($year == $v->year) ? $v->month <= $month : true;
            })->slice(0, 60)->values();

        return $revenue->values()->map(function ($v, $i) use ($revenue) {
            $ye = $revenue->where('year', $v->year - 1)
                ->where('month', $v->month)
                ->first();

            $v->yoy = is_null($ye) ? 0 : round((($v->value / $ye->value) - 1) * 100, 2);
            $v->qoq = isset($revenue[$i + 1]) ? round((($v->value / $revenue[$i + 1]->value) - 1) * 100, 2) : 0;

            return $v;
        })->filter(function ($value) {
            return $value->yoy != 0;
        })->values()->toArray();
    }
}
