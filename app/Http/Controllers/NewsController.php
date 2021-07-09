<?php

namespace App\Http\Controllers;

use App\Services\News;
use Illuminate\Http\Request;

class NewsController
{
    /**
     * @var News
     */
    private News $news;

    /**
     * NewsController constructor.
     *
     * @param News $news
     */
    public function __construct(News $news)
    {
        $this->news = $news;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.news', [
            'header' => [
                '標題',
                '時間',
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
                            'id' => 'title',
                            'type' => 'text',
                            'name' => '標題',
                        ],
                        [
                            'id' => 'publish_time',
                            'type' => 'text',
                            'name' => '時間',
                        ],
                        [
                            'id' => 'remark',
                            'type' => 'edit',
                            'name' => '備註',
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
        $data = $this->news->list($request->all());
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
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
            'result' => $this->news->update($id, $request->get('remark')),
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
            'result' => $this->news->delete($id),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        return response()->json([
            'result' => true,
            'count' => $this->news->clear(),
        ]);
    }
}
