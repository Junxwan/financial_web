<?php

namespace App\Http\Controllers\Revenue;

use App\Services\Revenue;
use Illuminate\Http\Request;

class RevenueController
{
    /**
     * @var Revenue
     */
    private Revenue $revenue;

    /**
     * RevenueController constructor.
     *
     * @param Revenue $revenue
     */
    public function __construct(Revenue $revenue)
    {
        $this->revenue = $revenue;
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
            $this->revenue->gets($code, $year),
        );
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(string $code, int $year, int $month)
    {
        return response()->json($this->revenue->recent($code, $year, $month));
    }

    /**
     * @param Request $request
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recents(Request $request, int $year, int $month)
    {
        return response()->json($this->revenue->recents(explode(',',$request->query('code')), $year, $month));
    }
}
