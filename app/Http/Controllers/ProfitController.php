<?php

namespace App\Http\Controllers;

use App\Services\Cash;
use App\Services\Profit;

class ProfitController
{
    /**
     * @var Profit
     */
    private $profit;

    /**
     * @var Cash
     */
    private $cash;

    /**
     * ProfitController constructor.
     *
     * @param Profit $profit
     * @param Cash $cash
     */
    public function __construct(Profit $profit, Cash $cash)
    {
        $this->profit = $profit;
        $this->cash = $cash;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.profit', [
            'quarterlys' => $this->profit->quarterlys(),
            'yearMonths' => $this->profit->yearMonths(),
            'business' => [
                [
                    'title' => '經營績效(成本、毛利、費用)',
                    'list' => [
                        [
                            'title' => '近12季成本(百萬)',
                            'name' => 'cost',
                        ],
                        [
                            'title' => '近12季毛利(百萬)',
                            'name' => 'gross',
                        ],
                        [
                            'title' => '近12季費用(百萬)',
                            'name' => 'fee',
                        ],
                    ],
                ],
                [
                    'title' => '經營績效(利益、業外、其他)',
                    'list' => [
                        [
                            'title' => '近12季利益(百萬)',
                            'name' => 'profit',
                        ],
                        [
                            'title' => '近12季業外(百萬)',
                            'name' => 'outside',
                        ],
                        [
                            'title' => '近12季其他(百萬)',
                            'name' => 'other',
                        ],
                    ],
                ],
                [
                    'title' => '經營績效(稅前、稅後、所得稅)',
                    'list' => [
                        [
                            'title' => '近12季稅前(百萬)',
                            'name' => 'profit_pre',
                        ],
                        [
                            'title' => '近12季稅後(百萬)',
                            'name' => 'profit_after',
                        ],
                        [
                            'title' => '近12季所得稅(百萬)',
                            'name' => 'tax',
                        ],
                    ],
                ],
                [
                    'title' => '經營績效(非控制、母權益)',
                    'list' => [
                        [
                            'title' => '近12季非控制(百萬)',
                            'name' => 'profit_non',
                        ],
                        [
                            'title' => '近12季母權益(百萬)',
                            'name' => 'profit_main',
                        ],
                    ],
                ],
                [
                    'title' => '研發/資本費用',
                    'list' => [
                        [
                            'title' => '近12季資本支出(百萬)',
                            'name' => 'real_estate',
                        ],
                        [
                            'title' => '近12季折舊(百萬)',
                            'name' => 'depreciation',
                        ],
                        [
                            'title' => '近12季研發(百萬)',
                            'name' => 'research',
                        ],
                    ],
                ],
            ],
        ]);
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

    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(string $code, int $year, int $quarterly)
    {
        $cash = $this->cash->recent($code, $year, $quarterly);
        $profit = $this->profit->recent($code, $year, $quarterly);

        return response()->json(
            $profit->map(function ($v) use ($cash) {
                $value = $cash->where('year', $v->year)
                    ->where('quarterly', $v->quarterly)
                    ->first();

                if (is_null($value)) {
                    $v->real_estate = 0;
                    $v->depreciation = 0;
                } else {
                    $v->real_estate = $value->real_estate * -1;
                    $v->depreciation = $value->depreciation;
                }

                return $v;
            })->values()
        );
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function eps(string $code)
    {
        return response()->json(
            $this->profit->eps($code)
        );
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dividend(string $code)
    {
        return response()->json(
            $this->profit->dividend($code)
        );
    }
}
