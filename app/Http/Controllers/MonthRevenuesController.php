<?php

namespace App\Http\Controllers;

use App\Repositories\RevenueRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthRevenuesController
{
    /**
     * @var string
     */
    private $view = 'page.month_revenues';

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        return view($this->view, [
            'year' => $now->year,
            'month' => $now->month,
        ]);
    }

    /**
     * @param Request $request
     * @param RevenueRepository $revenue
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(Request $request, RevenueRepository $revenue)
    {
        $now = Carbon::now();
        return $revenue->list(
            $request->get('year', $now->year),
            $request->get('month', $now->month),
        );
    }
}
