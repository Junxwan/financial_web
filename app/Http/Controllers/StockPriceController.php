<?php

namespace App\Http\Controllers;

use App\Services\Stock;
use Illuminate\Http\Request;

class StockPriceController
{
    /**
     * @var Stock
     */
    private Stock $stock;

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
                '收盤',
                '漲幅',
                '成交量',
                '成交金額',
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
     * StockPriceController constructor.
     *
     * @param Stock $stock
     */
    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $data = $this->stock->priceList($request->all());
        return response()->json([
            'draw' => $request->get('draw', 0),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
        ]);
    }
}
