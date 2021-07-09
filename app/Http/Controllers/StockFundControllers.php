<?php

namespace App\Http\Controllers;

use App\Services\Fund;

class StockFundControllers
{
    /**
     * FundController constructor.
     *
     * @param Fund $fund
     */
    public function __construct(Fund $fund)
    {
        $this->fund = $fund;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.stock_fund', [
            'year' => $this->fund->years(),
            'header' => [
                '基金',
                '比例',
            ],
        ]);
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(string $code, int $year)
    {
        return response()->json(
            $this->fund->stock($code, $year)
        );
    }
}
