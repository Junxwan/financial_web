<?php

namespace App\Repositories;

use App\Models\Profit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProfitRepository extends Repository
{
    /**
     * @param int $tag
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function codeByTag(int $tag, int $year, int $quarterly)
    {
        return Profit::query()->select(
            DB::RAW('stocks.code'),
            DB::RAW('stocks.name'),
            DB::RAW('profits.year'),
            DB::RAW('profits.quarterly'),
            DB::RAW('profits.revenue'),
            DB::RAW('profits.gross'),
            DB::RAW('profits.fee'),
            DB::RAW('profits.profit'),
            DB::RAW('profits.profit_pre'),
            DB::RAW('profits.profit_after'),
            DB::RAW('profits.outside'),
            DB::RAW('profits.profit_main'),
            DB::RAW('profits.eps'),
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->join('stock_tags', 'stock_tags.stock_id', '=', 'stocks.id')
            ->where('stock_tags.tag_id', $tag)
            ->whereBetween('profits.year', [$year - 1, $year])
            ->orderByDesc('profits.year')
            ->orderByDesc('profits.quarterly')
            ->get()
            ->groupBy('code')->map(function ($value) use ($year, $quarterly) {
                $value = (new Collection($value))->filter(function ($v) use ($year, $quarterly) {
                    return ($year == $v->year) ? $v->quarterly <= $quarterly : true;
                });

                $profit = q4r($value, [
                    'revenue',
                    'gross',
                    'profit',
                    'profit_pre',
                    'profit_after',
                    'profit_main',
                    'outside',
                    'eps',
                ]);

                $value = $profit->where('year', $year)->where('quarterly', $quarterly)->first();
                $ye = $profit->where('year', $year - 1)->where('quarterly', $quarterly)->first();
                $value->revenue_yoy = is_null($ye) ? 0 : round((($value->revenue / $ye->revenue) - 1) * 100, 2);
                $value->gross_yoy = round(($value->gross / $value->revenue) * 100, 2);
                $value->fee_r = round(($value->fee / $value->revenue) * 100, 2);
                $value->profit_r = round(($value->profit / $value->revenue) * 100, 2);
                $value->profit_after_r = round(($value->profit_after / $value->revenue) * 100, 2);
                $value->profit_pre_r = round(($value->profit_pre / $value->revenue) * 100, 2);
                $value->eps = round($value->eps, 2);
                $value->non_eps = round(($value->outside / $value->profit_main) * $value->eps, 2);
                return $value;
            })->sortBy('code')->values();
    }

    /**
     * @param int $year
     * @param int $quarterly
     * @param array $codes
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|Collection
     */
    public function quarterly(int $year, int $quarterly, array $codes)
    {
        return Profit::query()->select(
            DB::RAW('stocks.code'),
            DB::RAW('stocks.name'),
            DB::RAW('profits.revenue'),
            DB::RAW('ROUND(profits.gross_ratio, 2) as gross_ratio'),
            DB::RAW('ROUND(profits.fee_ratio, 2) as fee_ratio'),
            DB::RAW('ROUND(profits.profit_ratio, 2) as profit_ratio'),
            DB::RAW('ROUND(profits.profit_pre_ratio, 2) as profit_pre_ratio'),
            DB::RAW('ROUND(profits.profit_after_ratio, 2) as profit_after_ratio'),
            DB::RAW('ROUND(profits.eps, 2) as eps'),
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('profits.year', $year)
            ->where('profits.quarterly', $quarterly)
            ->whereIn('stocks.code', $codes)
            ->get()
            ->sortBy('code')
            ->values();
    }
}
