<?php

namespace App\Http\Controllers;

use App\Services\Profit;

class ProfitController
{
    /**
     * @var Profit
     */
    private Profit $profit;

    /**
     * ProfitController constructor.
     *
     * @param Profit $profit
     */
    public function __construct(Profit $profit)
    {
        $this->profit = $profit;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.profit', [
            'quarterlys' => $this->profit->quarterlys(),
            'yearMonths' => $this->profit->yearMonths(),
        ]);
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(string $code, int $year, int $quarterly)
    {
        //        return response()->json(Profit::query()
        //            ->select(
        //                DB::RAW('profits.*')
        //            )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
        //            ->where('stocks.code', $code)
        //            ->where('profits.year', $year)
        //            ->where('profits.season', $season)
        //            ->first(),
        //        );
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function year(string $code, int $year)
    {
        return response()->json(
            $this->profit->year($code, $year)
        );
    }
}
