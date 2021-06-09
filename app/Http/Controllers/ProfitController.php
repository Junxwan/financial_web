<?php

namespace App\Http\Controllers;

use App\Models\Profit;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class ProfitController
{
    /**
     * @param string $code
     * @param int $year
     * @param int $season
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(string $code, int $year, int $season)
    {
        return response()->json(Profit::query()
            ->select(
                DB::RAW('profits.*')
            )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', $year)
            ->where('profits.season', $season)
            ->first(),
        );
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function year(string $code, int $year)
    {
        return response()->json(
            Profit::query()
                ->select(
                    DB::RAW('profits.*')
                )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
                ->where('stocks.code', $code)
                ->where('profits.year', $year)
                ->get()
        );
    }
}
