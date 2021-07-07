<?php

namespace App\Services;

use App\Models\Dividend;
use App\Models\Profit as Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Profit
{
    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function year(string $code, int $year)
    {
        $data = Model::query()->select(
            DB::RAW('profits.*')
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', $year)
            ->get();

        return q4r($data, [
            'revenue',
            'gross',
            'fee',
            'outside',
            'other',
            'profit',
            'profit_pre',
            'profit_after',
            'tax',
            'profit_non',
            'profit_main',
            'eps',
        ]);
    }

    /**
     * 近四與三季eps總和
     *
     * @param int $codeId
     *
     * @return array
     */
    public function epsSum(int $codeId)
    {
        $profit = Model::query()
            ->select('year', 'quarterly', 'eps')
            ->where('stock_id', $codeId)
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->limit(5)
            ->get();

        $profit = q4r($profit, 'eps');
        $eps4Sum = round($profit->slice(0, 4)->sum('eps'), 2);

        if ($profit->count() < 4) {
            $eps3Sum = $eps4Sum;
        } else {
            $eps3Sum = round($profit->slice(0, 3)->sum('eps'), 2);
        }

        return [$eps4Sum, $eps3Sum];
    }

    /**
     * @return array
     */
    public function quarterlys()
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 0; $i < ($now->year - 2012); $i++) {
            $year = ($now->year - $i);
            $l = 4;

            if ($i == 0) {
                if ($now->month >= 11) {
                    $l = 3;
                } elseif ($now->month >= 8) {
                    $l = 2;
                } elseif ($now->month >= 5) {
                    $l = 1;
                }
            }

            $data[$year] = $l;
        }

        $s = [];

        foreach ($data as $k => $v) {
            for ($i = $v; $i >= 1; $i--) {
                $s[] = "{$k}-Q{$i}";
            }
        }

        return $s;
    }

    /**
     * @return array
     */
    public function yearMonths()
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 0; $i < ($now->year - 2012); $i++) {
            $year = ($now->year - $i);
            $l = 12;

            if ($i == 0) {
                $l = $now->month - 1;
            }

            $data[$year] = $l;
        }

        $s = [];

        foreach ($data as $k => $v) {
            for ($i = $v; $i >= 1; $i--) {
                $s[] = "{$k}-" . sprintf("%02d", $i);
            }
        }

        return $s;
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function recent(string $code, int $year, int $quarterly)
    {
        $profit = Model::query()->select(
            DB::RAW('profits.year'),
            DB::RAW('profits.quarterly'),
            DB::RAW('profits.revenue'),
            DB::RAW('profits.cost'),
            DB::RAW('profits.gross'),
            DB::RAW('profits.fee'),
            DB::RAW('profits.outside'),
            DB::RAW('profits.other'),
            DB::RAW('profits.profit'),
            DB::RAW('profits.tax'),
            DB::RAW('profits.profit_pre'),
            DB::RAW('profits.profit_after'),
            DB::RAW('profits.profit_main'),
            DB::RAW('profits.profit_non'),
            DB::RAW('profits.research'),
            DB::RAW('profits.eps'),
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', '<=', $year)
            ->orderByDesc('profits.year')
            ->orderByDesc('profits.quarterly')
            ->limit(24)
            ->get()->filter(function ($v) use ($year, $quarterly) {
                return ($year == $v->year) ? $v->quarterly <= $quarterly : true;
            });

        $profit = q4r($profit, [
            'revenue',
            'cost',
            'gross',
            'fee',
            'outside',
            'other',
            'profit',
            'tax',
            'profit_pre',
            'profit_after',
            'profit_main',
            'profit_non',
            'research',
        ]);

        return $profit->map(function ($v) use ($profit) {
            $ye = $profit->where('year', $v->year - 1)
                ->where('quarterly', $v->quarterly)
                ->first();

            $v->revenue_yoy = is_null($ye) ? 0 : round((($v->revenue / $ye->revenue) - 1) * 100, 2);

            return $v;
        });
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function eps(string $code)
    {
        $data = Model::query()->select(
            DB::RAW('profits.year'),
            DB::RAW('profits.eps'),
            DB::RAW('profits.quarterly')
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', '>=', Carbon::now()->year - 8)
            ->orderByDesc('profits.year')
            ->get()->groupBy('year');

        $eps = [];
        foreach ($data as $year => $value) {
            $q4 = $value->where('quarterly', 4)->first();

            $t = [
                'year' => $year,
                'eps' => is_null($q4) ? "" : $q4->eps,
            ];

            if (! is_null($q4)) {
                foreach (q4r($value, 'eps') as $v) {
                    $t["q{$v->quarterly}"] = round($v->eps, 2);
                }
            } else {
                for ($i = 1; $i <= 4; $i++) {
                    if (! is_null($v = $value->where('quarterly', $i)->first())) {
                        $t["q{$i}"] = round($v->eps, 2);
                    } else {
                        $t["q{$i}"] = "";
                    }
                }
            }

            $eps[] = $t;
        }

        return $eps;
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function dividend(string $code)
    {
        return Dividend::query()->select(
            DB::RAW('dividends.year'),
            DB::RAW('dividends.cash'),
        )->join('stocks', 'stocks.id', '=', 'dividends.stock_id')
            ->where('stocks.code', $code)
            ->orderByDesc('dividends.year')
            ->limit(8)
            ->get();
    }
}
