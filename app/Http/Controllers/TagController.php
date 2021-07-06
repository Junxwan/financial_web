<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class TagController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.tag', [
            'header' => [
                '名稱',
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
                            'id' => 'name',
                            'type' => 'text',
                            'name' => '名稱',
                        ],
                    ],
                ],
                [
                    'id' => 'create',
                    'title' => '新增',
                    'btn' => '新增',
                    'list' => [
                        [
                            'id' => 'name',
                            'type' => 'text',
                            'name' => '名稱',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $query = Tag::query()->select('id', 'name');
        $queryTotal = Tag::query();

        if (! is_null($search = $request->get('search'))) {
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
                $queryTotal = $this->whereLike($queryTotal->getQuery(), $search);
            }
        }

        $total = $queryTotal->count();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $query->offset($request->get('start'))
                ->limit($request->get('limit'))
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
            'result' => Tag::query()->insert([
                'name' => $request->get('name'),
            ]),
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
            'result' => (bool)Tag::query()->where('id', $id)->update([
                'name' => $request->get('name'),
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
            'result' => (bool)Tag::query()->where('id', $id)->delete(),
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
        return $query->where('name', 'like', "%{$data['value']}%");
    }
}
