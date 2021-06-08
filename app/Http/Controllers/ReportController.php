<?php

namespace App\Http\Controllers;

use App\Models\Equity;
use App\Models\Profit;
use App\Models\Report;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReportController
{
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
        $data = $request->all();
        $query = Report::query()->select(
            DB::raw('reports.id'),
            'date', 'title', 'code', 'name', 'price_f', 'action', 'eps_1', 'eps_2', 'eps_3', 'eps_4', 'pe'
        )->join('stocks', 'reports.stock_id', '=', 'stocks.id');
        $queryTotal = Report::query();

        if (isset($data['search'])) {
            $search = $data['search'];
            if (isset($search['value']) && ! empty($search['value'])) {
                switch ($search['name']) {
                    case 'code':
                        $query = $this->whereCode($query, $search['value']);
                        $queryTotal = $this->whereCode($queryTotal, $search['value'])
                            ->join('stocks', 'reports.stock_id', '=', 'stocks.id');
                        break;
                    case 'title':
                        $query = $this->whereLikeTitle($query, $search['value']);
                        $queryTotal = $this->whereLikeTitle($queryTotal, $search['value']);
                        break;
                }
            }

            $query = $this->whereDate($query, $search);
            $queryTotal = $this->whereDate($queryTotal, $search);
        }

        $total = $queryTotal->count();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $query->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderByDesc('date')
                ->get(),
        ]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(int $id)
    {
        $data = Report::query()
            ->join('stocks', 'reports.stock_id', '=', 'stocks.id')
            ->where(DB::raw('`reports`.`id`'), $id)
            ->first();

        if (is_null($data)) {
            return response('', Response::HTTP_NOT_FOUND);
        }

        $profit = Profit::query()
            ->select('eps')
            ->where('stock_id', $data->id)
            ->orderByDesc('year')
            ->orderByDesc('season')
            ->limit(4)
            ->get();

        $data['eps_4'] = round($profit->sum('eps'), 2);

        if ($profit->count() != 4) {
            $data['eps_3'] = $data['eps_4'];
        } else {
            $data['eps_3'] = round($profit->slice(0, 3)->sum('eps'), 2);
        }

        $equity = Equity::query()
            ->where('stock_id', $data->stock_id)
            ->orderByDesc('year')
            ->orderByDesc('season')
            ->first();

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
            'result' => Report::query()->insert($this->getData($request)),
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
            'result' => Report::query()->where('id', $id)->update($this->getData($request)),
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
            'result' => Report::query()->where('id', $id)->delete(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    private function getData(Request $request)
    {
        $data = $request->all();
        return array_merge([
            'stock_id' => Stock::query()->where('code', $data['code'])->first()->id,
            'title' => $data['title'],
            'date' => $data['date'],
            'price_f' => $data['price_f'],
            'season' => $data['season'],
            'month' => $data['month'],
            'action' => $data['action'],
            'value' => $data['value'],
            'market_eps_f' => $data['market_eps_f'],
            'pe' => $data['pe'],
            'evaluate' => $data['evaluate'],
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
            $data['eps'],
            $data['revenue'],
            $this->ar($data['gross']),
            $this->ar($data['fee']),
            $this->ar($data['outside']),
            $this->ar($data['other']),
            $this->ar($data['tax']),
            $this->ar($data['non']),
        );
    }

    /**
     * @param Builder $query
     * @param $value
     *
     * @return Builder
     */
    private function whereLikeTitle(Builder $query, $value)
    {
        return $query->where('title', 'like', "%{$value}%");
    }

    /**
     * @param Builder $query
     * @param $value
     *
     * @return Builder
     */
    private function whereCode(Builder $query, $value)
    {
        return $query->where(DB::raw('`stocks`.`code`'), $value);
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereDate(Builder $query, array $data)
    {
        if (! is_null($data['start_date']) && ! is_null($data['end_date'])) {
            return $query->whereBetween('date', [$data['start_date'], $data['end_date']]);
        } elseif (! is_null($data['start_date'])) {
            return $query->where('date', '<=', "{$data['start_date']} 23:59:59");
        } elseif (! is_null($data['end_date'])) {
            return $query->where('date', '>=', "{$data['end_date']} 00:00:00");
        }

        return $query;
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
        ];
    }
}
