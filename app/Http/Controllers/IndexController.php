<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class IndexController extends BaseController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('index', [
            'list' => [],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function keyWord(Request $request)
    {
        $key = "%{$request->get('key')}%";

        $data = DB::select("SELECT * FROM financial.news WHERE context LIKE ? or title LIKE ? ORDER BY publish_time DESC",
            [$key, $key]
        );

        return view('index', [
            'list' => $data,
        ]);
    }
}
