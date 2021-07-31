<?php

namespace App\Http\Controllers;

use App\Services\Price;
use Illuminate\Http\Request;

class PriceController
{
    /**
     * @var Price
     */
    private Price $price;

    /**
     * PriceController constructor.
     *
     * @param Price $price
     */
    public function __construct(Price $price)
    {
        $this->price = $price;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.price', [
            'header' => [
                '代碼',
                '名稱',
                '收盤',
                '漲幅',
                '成交量',
                '成交值',
                '投信金額',
                '外資金額',
                '市場',
                '類股',
                '週%',
                '月%',
                '季%',
                '標籤',
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
        $data = $this->price->list($request->all());
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
        ]);
    }
}
