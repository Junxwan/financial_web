<?php

namespace App\Http\Controllers;

use App\Services\Equity;
use App\Services\Profit;
use App\Services\Report;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController
{
    /**
     * @var Report
     */
    private Report $report;

    /**
     * ReportController constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.report.index', [
            'header' => [
                '代碼',
                '名稱',
                '標題',
                '預估EPS',
                '預估股價',
                '方向',
                '日期',
                '編輯',
                '刪除',
            ],
            'modal' => [],
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createView()
    {
        return view('page.report.create', $this->viewData());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $data = $this->report->list($request->all());
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
        ]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Profit $profit, Equity $equity, int $id)
    {
        $data = $this->report->get($id);

        if (is_null($data)) {
            return response('', Response::HTTP_NOT_FOUND);
        }

        [$eps4, $eps3] = $profit->epsSum($id);
        $data['eps3_sum'] = $eps3;
        $data['eps4_sum'] = $eps4;

        $equity = $equity->get($data->stock_id);

        $data['capital'] = round($data['capital'] / 1000);
        $data['start_stock'] = round($equity->start_stock / 1000);

        return view('page.report.create', array_merge($this->viewData(), [
            'data' => $data,
            'id' => $id,
        ]));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        return response()->json([
            'result' => $this->report->insert($request),
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        return response()->json([
            'result' => $this->report->update($id, $request),
        ]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $id)
    {
        return response()->json([
            'result' => $this->report->delete($id),
        ]);
    }

    /**
     * @return array[]
     */
    private function viewData()
    {
        return [
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
                        'id' => 'profit_non',
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
                        'id' => 'profit_pre',
                        'name' => '稅前(百萬)',
                        'readonly' => true,
                    ],
                    [
                        'id' => 'profit_after',
                        'name' => '稅後(百萬)',
                        'readonly' => true,
                    ],
                ],
                [
                    [
                        'id' => 'profit_main',
                        'name' => '母權益(百萬)',
                        'readonly' => true,
                    ],
                    [],
                    [],
                ],
            ],
        ];
    }
}
