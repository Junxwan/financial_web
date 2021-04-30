<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class IndexController extends BaseController
{
    private $limit = 100;

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 0);
        $key = $request->get('key', '');
        $sql = 'SELECT title,url,publish_time FROM news ';
        $bindings = [];

        if ($key != '') {
            $key = "%{$key}%";
            $bindings = [
                $key,
                $key,
            ];

            $sql .= "WHERE context LIKE ? or title LIKE ? ";
        }

        $data = DB::select($sql . "ORDER BY publish_time DESC LIMIT ? OFFSET ?",
            array_merge($bindings, [$this->limit, $this->limit * $page])
        );

        return view('index', [
            'list' => $data,
            'page' => $page,
            'prev_page' => $page > 1 ? $page - 1 : 1,
            'next_page' => $page == 0 ? 2 : $page + 1,
            'key' => $request->get('key', ''),
        ]);
    }

    public function info()
    {
        echo "null total:" . DB::select("SELECT count(1) as count FROM news where context is Null")[0]->count . "<br>";
        echo "total:" . DB::select("SELECT count(1) as count FROM news")[0]->count;
    }
}
