<?php

namespace App\Repositories;

use App\Models\Fund\Fund;
use App\Models\Fund\Detail;
use App\Models\Fund\Stock;
use Illuminate\Support\Facades\DB;

class FundRepository extends Repository
{
    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByCompany(int $id)
    {
        return Fund::query()->select('id', 'name')->where('company_id', $id)->get();
    }

    /**
     * @param int $year
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function stocks(int $year, int $id)
    {
        return Stock::query()->select(
            'fund_stocks.id', 'fund_stocks.year', 'fund_stocks.month', 'funds.name', 'stocks.code',
            'stocks.name', 'fund_stocks.amount', DB::RAW('ROUND(fund_stocks.ratio, 2) AS ratio')
        )->join('stocks', 'stocks.id', '=', 'fund_stocks.stock_id')
            ->join('funds', 'fund_stocks.fund_id', '=', 'funds.id')
            ->where('fund_id', $id)
            ->where('year', $year)
            ->orderByDesc('fund_stocks.year')
            ->orderByDesc('fund_stocks.month')
            ->orderByDesc('fund_stocks.ratio')
            ->get()->groupBy('month')->values();
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    public function stock(string $code, int $year)
    {
        return Stock::query()->select(
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
            ->values()[0];
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function scale(int $id)
    {
        return Detail::query()
            ->select('year', 'month', 'scale')
            ->where('fund_id', $id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function value(int $id)
    {
        return Detail::query()
            ->select('year', 'month', 'value')
            ->where('fund_id', $id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }
}
