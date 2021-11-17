<?php

namespace App\Http\Controllers\Stock;

use App\Models\Stock\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.stock.price', [
            'header' => [
                '代碼',
                '名稱',
                '日期',
                '收盤',
                '漲幅',
                '成交值',
                '投信金額',
                '外資金額',
                '市場',
                '類股',
                '週%',
                '月%',
                '季%',
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
        if (! is_null($search = $request->get('search')) && isset($search['value']) && ! empty($search['value'])) {
            $queryTotal = Price::query()->join('stocks', 'stocks.id', '=', 'prices.stock_id');
            $query = Price::query()->select(
                'stocks.code', 'stocks.name', 'prices.date', 'prices.close', 'prices.fund_value',
                'prices.foreign_value',
                DB::RAW('ROUND(prices.increase, 2) AS increase'), 'prices.volume', 'prices.value', 'stocks.market',
                DB::RAW('classifications.name AS cName'), DB::RAW('ROUND(prices.increase_5,2) AS increase_5'),
                DB::RAW('ROUND(prices.increase_23,2) AS increase_23'),
                DB::RAW('ROUND(prices.increase_63,2) AS increase_63')
            )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
                ->join('classifications', 'classifications.id', '=', 'stocks.classification_id');

            if ($request->has('search.start_date') && ! is_null($date = $request->get('search')['start_date'])) {
                $query = $query->where('date', '<=', $date);
                $queryTotal = $queryTotal->where('date', '<=', $date);
            } else {
                $query = $this->latestDate($query);
                $queryTotal = $this->latestDate($queryTotal);
            }

            $query = $this->whereLike($query, $search);
            $queryTotal = $this->whereLike($queryTotal, $search);

            $total = $queryTotal->count();

            $data = $query
                ->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderByDesc($request->get('order', 'prices.date'))
                ->get();
        } else {
            $total = 0;
            $data = [];
        }


        return response()->json([
            'draw' => $request->get('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function date()
    {
        return response()->json([
            'date' => \App\Models\Stock\Price::query()->select('date')->orderByDesc('date')->first()->date,
        ]);
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereLike(Builder $query, array $data)
    {
        return $query->where('stocks.code', 'like', "{$data['value']}%")
            ->orWhere('stocks.name', 'like', "%{$data['value']}%");
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    private function latestDate(Builder $query)
    {
        return $query->where('prices.date', '<=', function ($query) {
            $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
        });
    }
}
