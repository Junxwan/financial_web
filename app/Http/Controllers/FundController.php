<?php

namespace App\Http\Controllers;

use App\Models\Fund as Model;
use App\Models\Company;
use App\Models\FundStock;
use App\Services\Fund;
use Illuminate\Support\Facades\DB;

class FundController
{
    private Fund $fund;

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
        $company = Company::query()->select('id', 'name')->get();

        return view('page.fund', [
            'ym' => $this->fund->years(),
            'company' => $company,
            'fund' => Model::query()->select('id', 'name')->where('company_id', $company[0]->id)->get(),
        ]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function funds(int $id)
    {
        return response()->json(
            Model::query()->select('id', 'name')->where('company_id', $id)->get()
        );
    }

    /**
     * @param int $year
     * @param int $fundId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stocks(int $year, int $fundId)
    {
        return response()->json(
            FundStock::query()->select(
                'fund_stocks.id', 'fund_stocks.year', 'fund_stocks.month', 'funds.name', 'stocks.code',
                'stocks.name', 'fund_stocks.amount', DB::RAW('ROUND(fund_stocks.ratio, 2) AS ratio')
            )->join('stocks', 'stocks.id', '=', 'fund_stocks.stock_id')
                ->join('funds', 'fund_stocks.fund_id', '=', 'funds.id')
                ->where('fund_id', $fundId)
                ->where('year', $year)
                ->orderByDesc('fund_stocks.year')
                ->orderByDesc('fund_stocks.month')
                ->orderByDesc('fund_stocks.ratio')
                ->get()->groupBy('month')->values()
        );
    }
}
