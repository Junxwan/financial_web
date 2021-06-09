<?php

namespace App\Services;

class Profit
{
    /**
     * 近四與三季eps總和
     *
     * @param int $codeId
     *
     * @return array
     */
    public function epsSum(int $codeId)
    {
        $profit = \App\Models\Profit::query()
            ->select('year', 'season', 'eps')
            ->where('stock_id', $codeId)
            ->orderByDesc('year')
            ->orderByDesc('season')
            ->limit(5)
            ->get();

        $profit = q4r($profit, 'eps');
        $eps4 = round($profit->slice(0, 4)->sum('eps'), 2);

        if ($profit->count() < 4) {
            $eps3 = $eps4;
        } else {
            $eps3 = round($profit->slice(0, 3)->sum('eps'), 2);
        }

        return [$eps4, $eps3];
    }
}
