<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
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
        $where = [];
        $startDate = null;
        $endDate = null;

        if ($key != '') {
            $key = "%{$key}%";
            $bindings = [
                $key,
                $key,
            ];

            $where[] = "(context LIKE ? or title LIKE ?) ";
        }

        $startDate = $request->get('start_date', null);
        $endDate = $request->get('end_date', null);

        if (! is_null($startDate) && ! is_null($endDate)) {
            $where[] = "publish_time between ? AND ? ";
            $bindings = array_merge($bindings, [$endDate, $startDate]);
        } elseif (! is_null($startDate)) {
            $where[] = "publish_time <= ? ";
            $bindings = array_merge($bindings, [$startDate]);
        } elseif (! is_null($endDate)) {
            $where[] = "publish_time >= ? ";
            $bindings = array_merge($bindings, [$endDate]);
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $data = DB::select($sql . " ORDER BY publish_time DESC LIMIT ? OFFSET ?",
            array_merge($bindings, [$this->limit, $this->limit * $page])
        );

        return view('index', [
            'list' => $data,
            'page' => $page,
            'prev_page' => $page > 1 ? $page - 1 : 1,
            'next_page' => $page == 0 ? 2 : $page + 1,
            'key' => $request->get('key', ''),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function info()
    {
        echo "null total:" . DB::select("SELECT count(1) as count FROM news where context is Null")[0]->count . "<br>";
        echo "total:" . DB::select("SELECT count(1) as count FROM news")[0]->count;
    }
}
