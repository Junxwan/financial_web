<?php

namespace App\Http\Controllers\Stock;

use App\Models\FundStock;
use App\Services\Fund;
use Illuminate\Support\Facades\DB;

class FundControllers
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
        return view('page.stock.fund', [
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
        $data = FundStock::query()->select(
            'fund_stocks.month', DB::RAW('funds.name AS fName'),
            'stocks.name', DB::RAW('ROUND(fund_stocks.ratio, 2) AS ratio')
        )->join('stocks', 'stocks.id', '=', 'fund_stocks.stock_id')
            ->join('funds', 'fund_stocks.fund_id', '=', 'funds.id')
            ->where('year', $year)
            ->where('stocks.code', $code)
            ->orderBy('fund_stocks.month')
            ->orderByDesc('fund_stocks.ratio')
            ->get()
            ->groupBy('month')
            ->groupBy('ratio')
            ->values();

        return response()->json(
            count($data) > 0 ? $data[0] : []
        );
    }
}
