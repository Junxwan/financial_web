<?php

namespace App\Http\Controllers\Financial;

class ForecastController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.profit.forecast', [
            'column' => [
                'revenue' => '營收',
                'cost' => '成本',
                'gross' => '毛利',
                'fee' => '費用',
                'profit' => '利益',
                'outside' => '業外',
                'other' => '其他',
                'profit_pre' => '稅前',
                'profit_after' => '稅後',
                'tax' => '所得稅',
                'profit_non' => '非控制',
                'profit_main' => '母權益',
                'value' => '股本',
                'eps' => 'EPS',
            ],
            'year' => date('Y'),
        ]);
    }
}
