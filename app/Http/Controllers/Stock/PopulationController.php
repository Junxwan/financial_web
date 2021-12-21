<?php

namespace App\Http\Controllers\Stock;

use App\Repositories\PopulationRepository;
use Illuminate\Http\Request;

class PopulationController
{
    /**
     * @var PopulationRepository
     */
    private PopulationRepository $repo;

    /**
     * PopulationController constructor.
     *
     * @param PopulationRepository $repo
     */
    public function __construct(PopulationRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.stock.population', [
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
        $data = $this->repo->list($request->all());
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
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
            'result' => $this->repo->insert($request->all()),
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
            'result' => $this->repo->update($id, $request->all()),
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
            'result' => $this->repo->delete($id),
        ]);
    }
}
