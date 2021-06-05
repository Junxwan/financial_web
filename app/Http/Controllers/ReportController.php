<?php

namespace App\Http\Controllers;

class ReportController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.report', [
            'q' => [
                [
                    [
                        'id' => 'g',
                        'name' => '毛利(百萬)',
                        'value' => 'gross',
                        'editor' => 'gross',
                    ],
                    [
                        'id' => 'c',
                        'name' => '費用(百萬)',
                        'value' => 'cost',
                        'editor' => 'cost',
                    ],
                    [
                        'id' => 'o',
                        'name' => '業外(百萬)',
                        'value' => 'outside',
                        'editor' => 'outside',
                    ],
                ],
                [
                    [
                        'id' => 'i',
                        'name' => '其他收益(百萬)',
                        'value' => 'other',
                        'editor' => 'other',
                    ],
                    [
                        'id' => 't',
                        'name' => '所得稅(百萬)',
                        'value' => 'tax',
                        'editor' => 'tax',
                    ],
                    [
                        'id' => 'n',
                        'name' => '非控制權益(百萬)',
                        'value' => 'non',
                        'editor' => 'non',
                    ],
                ],
                [
                    [
                        'id' => 'p',
                        'name' => '利益(百萬)',
                        'value' => 'profit',
                        'readonly' => true,
                    ],
                    [
                        'id' => 'pb',
                        'name' => '稅前(百萬)',
                        'value' => 'profitb',
                        'readonly' => true,
                    ],
                    [
                        'id' => 'pa',
                        'name' => '稅後(百萬)',
                        'value' => 'profita',
                        'readonly' => true,
                    ],
                ],
                [
                    [
                        'id' => 'm',
                        'name' => '母權益(百萬)',
                        'value' => 'main',
                        'readonly' => true,
                    ],
                    [],
                    [],
                ],
            ],
        ]);
    }

    public function list()
    {
        //        return response()->json([
        //            'draw' => $request->get('draw'),
        //            'recordsTotal' => $total,
        //            'recordsFiltered' => $total,
        //            'data' => $query->offset($request->get('start'))
        //                ->limit($request->get('limit'))
        //                ->orderByDesc('publish_time')
        //                ->get(),
        //        ]);
    }
}
