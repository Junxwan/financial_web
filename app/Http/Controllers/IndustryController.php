<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndustryController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.industry', [
            'header' => [
                '代碼',
                '名稱',
                '漲幅',
                '成交占比',
                '成交值',
            ],
            'modal' => [],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        if ($request->has('search.name')) {
            $code = $request->get('search')['name'];
        } else {
            $code = 'TSE';
        }

        $query = Price::query()
            ->select(
                DB::RAW('stocks.code'),
                DB::RAW('stocks.name'),
                DB::RAW('prices.date'),
                DB::RAW('prices.increase'),
                DB::RAW('prices.volume'),
                DB::RAW('ROUND(prices.volume_ratio, 2) AS volume_ratio'),
            )
            ->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->where('stocks.code', 'like', "{$code}%");

        if ($request->has('search.start_date') && ! is_null($date = $request->get('search')['start_date'])) {
            $query = $query->where('date', $date);
        } else {
            $query = $query->where('date', function ($query) {
                $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
            });
        }

        $data = $query->orderByDesc($request->get('order', 'increase'))
            ->get()
            ->whereNotIn('code', $code)
            ->values();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data->count(),
            'recordsFiltered' => $data->count(),
            'data' => $data,
        ]);
    }
}
