<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Models\Stock;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $ct = Classification::query()->select(DB::raw('id as value'), 'name')->get();

        return view('page.stock', [
            'header' => [
                '代碼',
                '名稱',
                '產業',
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
        $query = Stock::query()->select('id', 'code', 'name', 'classification_id');
        $queryTotal = Stock::query()->getQuery();

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
