<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Models\Profit;
use App\Models\Stock;
use App\Services\Profit as Service;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StockController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $ct = Classification::query()->select(DB::raw('id as value'), 'name')->get();
        $o = new \stdClass();
        $o->name = '上櫃';
        $o->value = 2;

        $t = new \stdClass();
        $t->name = '上市';
        $t->value = 1;

        $market = [$t, $o];

        return view('page.stock', [
            'header' => [
                '代碼',
                '名稱',
                '產業',
                '市場',
                '編輯',
                '刪除',
            ],
            'modal' => [
                [
                    'id' => 'edit',
                    'title' => '編輯',
                    'btn' => '更新',
                    'list' => [
                        [
                            'id' => 'code',
                            'type' => 'text',
                            'name' => '代碼',
                        ],
                        [
                            'id' => 'name',
                            'type' => 'text',
                            'name' => '名稱',
                        ],
                        [
                            'id' => 'classification_id',
                            'type' => 'select',
                            'name' => '產業',
                            'value' => $ct,
                        ],
                        [
                            'id' => 'market',
                            'type' => 'select',
                            'name' => '市場',
                            'value' => $market,
                        ],
                    ],
                ],
                [
                    'id' => 'create',
                    'title' => '新增',
                    'btn' => '新增',
                    'list' => [
                        [
                            'id' => 'code',
                            'type' => 'text',
                            'name' => '代碼',
                        ],
                        [
                            'id' => 'name',
                            'type' => 'text',
                            'name' => '名稱',
                        ],
                        [
                            'id' => 'classification_id',
                            'type' => 'select',
                            'name' => '產業',
                            'value' => $ct,
                        ],
                        [
                            'id' => 'market',
                            'type' => 'select',
                            'name' => '市場',
                            'value' => $market,
                        ],
                    ],
                ],
            ],
            'classification' => $ct,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $query = Stock::query()->select('id', 'code', 'name', 'classification_id', 'market')
            ->whereIn('market', [1, 2]);
        $queryTotal = Stock::query()->whereIn('market', [1, 2])->getQuery();

        if (! is_null($search = $request->get('search'))) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
                $queryTotal = $this->whereLike($queryTotal, $search);
            }
        }

        $total = $queryTotal->count();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $query->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderBy('code')
                ->get(),
        ]);
    }

    /**
     * @param Service $profit
     * @param string $code
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function search(Service $profit, string $code)
    {
        $data = Stock::query()
            ->select(
                DB::Raw('stocks.id AS id'),
                'name',
                'capital',
                DB::Raw('equities.start_stock AS start_capital'),
                'year',
                'quarterly'
            )
            ->leftJoin('equities', 'equities.stock_id', '=', 'stocks.id')
            ->where('code', $code)
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->first();

        if (is_null($data)) {
            return response('', Response::HTTP_NOT_FOUND);
        }

        [$eps4, $eps3] = $profit->epsSum($data->id);
        $data['eps4_sum'] = $eps4;
        $data['eps3_sum'] = $eps3;

        return response()->json($data);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        return response()->json([
            'result' => Stock::insert([
                'code' => $request->get('code'),
                'name' => $request->get('name'),
                'classification_id' => $request->get('classification_id'),
                'market' => $request->get('market'),
            ]),
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
            'result' => Stock::query()->where('id', $id)->delete(),
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
            'result' => Stock::query()->where('id', $id)->update([
                'code' => $request->get('code'),
                'name' => $request->get('name'),
                'classification_id' => $request->get('classification_id'),
                'market' => $request->get('market'),
            ]),
        ]);
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereLike(Builder $query, array $data)
    {
        return $query->where('code', 'like', "{$data['value']}%")
            ->orWhere('name', 'like', "%{$data['value']}%");
    }
}
