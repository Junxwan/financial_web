<?php

namespace App\Services;

use \App\Models\Profit as Model;
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
}
