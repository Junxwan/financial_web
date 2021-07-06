<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Controller
{
    /**
     * @var string
     */
    protected $view = '';

    /**
     * @var array
     */
    protected $header = [];

    /**
     * @var mixed
     */
    protected $repo;

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function initView(Request $request)
    {
        return [];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function createColumns(array $data)
    {
        return [];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function editColumns(array $data)
    {
        return $this->createColumns($data);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $data = $this->initView($request);

        return view($this->view, array_merge([
            'header' => $this->header,
            'modal' => [
                [
                    'id' => 'create',
                    'title' => '新增',
                    'btn' => '新增',
                    'list' => $this->createColumns($data),
                ],
                [
                    'id' => 'edit',
                    'title' => '編輯',
                    'btn' => '更新',
                    'list' => $this->editColumns($data),
                ],
            ],
        ], $data));
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
            'data' => $data['data'],
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
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
            'result' => $this->repo->create($request->all()),
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
