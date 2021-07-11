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
            DB::RAW('profits.profit'),
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
                    'profit_after',
                    'profit_main',
                    'outside',
                    'eps',
                ]);

                $value = $profit->where('year', $year)->where('quarterly', $quarterly)->first();
                $ye = $profit->where('year', $year - 1)->where('quarterly', $quarterly)->first();
                $value->revenue_yoy = is_null($ye) ? 0 : round((($value->revenue / $ye->revenue) - 1) * 100, 2);
                $value->gross_yoy = round(($value->gross / $value->revenue) * 100, 2);
                $value->profit_yoy = round(($value->profit / $value->revenue) * 100, 2);
                $value->profit_after_yoy = round(($value->profit_after / $value->revenue) * 100, 2);
                $value->eps = round($value->eps, 2);
                $value->non_eps = round(($value->outside / $value->profit_main) * $value->eps, 2);
                return $value;
            })->sortBy('code')->values();
    }
}
