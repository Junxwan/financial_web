<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Stock;
use Illuminate\Http\Request;

class ReportController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.report', [
            'qs' => [
                [
                    [
                        'id' => 'gross',
                        'name' => '毛利(百萬)',
                        'editor' => true,
                    ],
                    [
                        'id' => 'fee',
                        'name' => '費用(百萬)',
                        'editor' => true,
                    ],
                    [
                        'id' => 'outside',
                        'name' => '業外(百萬)',
                        'editor' => true,
                    ],
                ],
                [
                    [
                        'id' => 'other',
                        'name' => '其他收益(百萬)',
                        'editor' => true,
                    ],
                    [
                        'id' => 'tax',
                        'name' => '所得稅(百萬)',
                        'editor' => true,
                    ],
                    [
                        'id' => 'non',
                        'name' => '非控制權益(百萬)',
                        'editor' => true,
                    ],
                ],
                [
                    [
                        'id' => 'profit',
                        'name' => '利益(百萬)',
                        'readonly' => true,
                    ],
                    [
                        'id' => 'profitB',
                        'name' => '稅前(百萬)',
                        'readonly' => true,
                    ],
                    [
                        'id' => 'profitA',
                        'name' => '稅後(百萬)',
                        'readonly' => true,
                    ],
                ],
                [
                    [
                        'id' => 'main',
                        'name' => '母權益(百萬)',
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

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $insert = array_merge([
            'code_id' => Stock::query()->where('code', $data['code'])->first()->id,
            'title' => $data['title'],
            'date' => $data['date'],
            'action' => $data['action'],
            'market_eps_f' => $data['market_eps_f'],
            'open_date' => $data['open_date'],
            'pe' => $data['pe'],
            'desc' => $data['desc'],
            'desc_total' => $data['desc_total'],
            'desc_revenue' => $data['desc_revenue'],
            'desc_gross' => $data['desc_gross'],
            'desc_fee' => $data['desc_fee'],
            'desc_outside' => $data['desc_outside'],
            'desc_other' => $data['desc_other'],
            'desc_tax' => $data['desc_tax'],
            'desc_non' => $data['desc_non'],
        ],
            $data['revenue'],
            $this->ar($data['gross']),
            $this->ar($data['fee']),
            $this->ar($data['outside']),
            $this->ar($data['other']),
            $this->ar($data['tax']),
            $this->ar($data['non']),
        );

        return response()->json([
            'result' => Report::query()->insert($insert),
        ]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function ar(array $data)
    {
        $d = [];
        foreach ($data as $k => $v) {
            $d[substr($k, 2)] = $v;
        }

        return $d;
    }
}
