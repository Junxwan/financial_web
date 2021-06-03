<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsController
{
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
        $total = DB::table('news')->count();
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => DB::table('news')
                ->select('title', 'publish_time', 'url')
                ->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderByDesc('publish_time')
                ->get(),
        ]);
    }

    public function delete()
    {

    }

    public function update()
    {

    }
}
