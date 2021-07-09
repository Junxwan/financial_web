<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\Fund;

class FundController
{
    /**
     * @var Fund
     */
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
        $fund = [];

        if (count($company) > 0) {
            $fund = $this->fund->getByCompany($company[0]->id);
        }

        return view('page.fund', [
            'year' => $this->fund->years(),
            'company' => $company,
            'fund' => $fund,
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
            $this->fund->getByCompany($id)
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
            $this->fund->stocks($year, $fundId)
        );
    }
}
