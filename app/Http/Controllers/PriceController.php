<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController
{
    /**
     * @var int[]
     */
    private $market = [
        'TSE' => 1,
        'OTC' => 2,
    ];

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.price', [
            'header' => [
                '代碼',
                '名稱',
                '開盤',
                '收盤',
                '最高',
                '最低',
                '漲幅',
                '成交量',
                '成交金額',
                '市場',
                '類股',
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
        $queryTotal = Price::query()->join('stocks', 'stocks.id', '=', 'prices.stock_id');
        $query = Price::query()->select(
            'stocks.code', 'stocks.name', 'prices.open', 'prices.close', 'prices.high', 'prices.low',
            DB::RAW('ROUND(prices.increase, 2) AS increase'), 'prices.volume', 'prices.value', 'stocks.market',
            DB::RAW('classifications.name AS cName')
        )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id',)
            ->whereIn('stocks.market', [1, 2]);

        if ($request->has('search.start_date') && ! is_null($date = $request->get('search')['start_date'])) {
            $query = $query->where('date', $date);
            $queryTotal = $queryTotal->where('date', $date);
        } else {
            $query = $this->latestDate($query);
            $queryTotal = $this->latestDate($queryTotal);
        }

        if ($request->has('search.name') && ! is_null($name = $request->get('search')['name'])) {
            $query = $query->where('stocks.market', $this->market[$name]);
            $queryTotal = $queryTotal->where('stocks.market', $this->market[$name]);
        }

        $total = $queryTotal->count();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $query
                ->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderByDesc($request->get('order', 'increase'))
                ->get(),
        ]);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    private function latestDate(Builder $query)
    {
        return $query->where('date', function ($query) {
            $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
        });
    }
}
