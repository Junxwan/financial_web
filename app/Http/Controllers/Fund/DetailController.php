<?php

namespace App\Http\Controllers\Fund;

use App\Models\Company;
use App\Services\Fund;

class DetailController
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

        return view('page.fund.detail', [
            'company' => $company,
            'fund' => $fund,
        ]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function scale(int $id)
    {
        return response()->json($this->fund->scale($id));
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function value(int $id)
    {
        return response()->json($this->fund->value($id));
    }
}
