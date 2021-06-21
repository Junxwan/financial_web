<?php

namespace App\Http\Controllers;

use App\Models\FundStock;
use App\Services\Fund;
use Illuminate\Support\Facades\DB;

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
                '個股',
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
            FundStock::query()->select(
                'fund_stocks.id', 'fund_stocks.year', 'fund_stocks.month', 'funds.name', 'stocks.code',
                'stocks.name', 'fund_stocks.amount', DB::RAW('ROUND(fund_stocks.ratio, 2) AS ratio')
            )->join('stocks', 'stocks.id', '=', 'fund_stocks.stock_id')
                ->join('funds', 'fund_stocks.fund_id', '=', 'funds.id')
                ->where('year', $year)
                ->where('stocks.code', $code)
                ->orderByDesc('fund_stocks.year')
                ->orderByDesc('fund_stocks.month')
                ->orderByDesc('fund_stocks.ratio')
                ->get()
                ->groupBy('month')
                ->groupBy('ratio')
                ->values()
        );
    }
}
