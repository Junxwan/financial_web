<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockPriceController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.stock_price', [
            'header' => [
                '代碼',
                '名稱',
                '日期',
                '開盤',
                '收盤',
                '漲幅',
                '成交量',
                '成交金額',
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
        $queryTotal = Price::query()->join('stocks', 'stocks.id', '=', 'prices.stock_id');
        $query = Price::query()->select(
            'stocks.code', 'stocks.name', 'prices.date', 'prices.open', 'prices.close',
            DB::RAW('ROUND(prices.increase, 2) AS increase'), 'prices.volume', 'prices.value', 'stocks.market',
            DB::RAW('classifications.name AS cName'), DB::RAW('ROUND(prices.increase_5,2) AS increase_5'),
            DB::RAW('ROUND(prices.increase_23,2) AS increase_23'),
            DB::RAW('ROUND(prices.increase_63,2) AS increase_63')
        )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id',)
            ->whereIn('stocks.market', [1, 2]);

        if ($request->has('search.start_date') && ! is_null($date = $request->get('search')['start_date'])) {
            $query = $query->where('date', '>=', $date);
            $queryTotal = $queryTotal->where('date', '>=', $date);
        } else {
            $query = $this->latestDate($query);
            $queryTotal = $this->latestDate($queryTotal);
        }

        if (! is_null($search = $request->get('search'))) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
                $queryTotal = $this->whereLike($queryTotal, $search);
            }
        }

        $total = $queryTotal->count();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $query
                ->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderByDesc($request->get('order', 'prices.date'))
                ->get(),
        ]);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
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
        return $query->where('prices.date', '>=', function ($query) {
            $query->select('date')->from('prices')->orderByDesc('id')->limit(1);
        });
    }
}
