<?php

namespace App\Http\Controllers\Financial;

use App\Services\Cash;

class CashController
{
    /**
     * @var Cash
     */
    private Cash $cash;

    /**
     * CashController constructor.
     *
     * @param Cash $cash
     */
    public function __construct(Cash $cash)
    {
        $this->cash = $cash;
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(string $code, int $year, int $quarterly)
    {
        return response()->json(
            $this->cash->recent($code, $year, $quarterly)
        );
    }
}
