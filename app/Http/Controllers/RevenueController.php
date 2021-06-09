<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class RevenueController
{
    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function year(string $code, int $year)
    {
        return response()->json(
            Revenue::query()
                ->select(
                    DB::RAW('revenues.*')
                )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
                ->where('stocks.code', $code)
                ->where('revenues.year', $year)
                ->get()
        );
    }
}
