<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\TestFixture\func;

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
        $data = $request->all();
        $query = DB::table('news')
            ->select('id', 'title', 'publish_time', 'url', 'remark');
        $queryTotal = DB::table('news');

        if (isset($data['search'])) {
            $search = $data['search'];
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query, $search);
                $queryTotal = $this->whereLike(DB::table('news'), $search);
            }

            $query = $this->whereDate($query, $search);
            $queryTotal = $this->whereDate($queryTotal, $search);
        }

        $total = $queryTotal->count();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $query->offset($request->get('start'))
                ->limit($request->get('limit'))
                ->orderByDesc('publish_time')
                ->get(),
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
            'result' => DB::table('news')->where('id', $id)->delete(),
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
            'result' => DB::table('news')->where('id', $id)->update([
                'remark' => $request->get('remark'),
            ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        $f = function ($name) {
            return DB::transaction(function () use ($name) {
                DB::statement("SET SQL_SAFE_UPDATES = 0;");
                return DB::table('news')->whereIn('id', function (Builder $query) use ($name) {
                    $query->fromSub(function (Builder $query2) use ($name) {
                        $query2->select('id')
                            ->from('news')
                            ->groupBy([$name])
                            ->having(DB::raw('count(*)'), '>', 1);
                    }, 'p');
                })->delete();
            });
        };

        return response()->json([
            'result' => true,
            'count' => $f('url') + $f('title'),
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
        return $query->where('context', 'like', "%{$data['value']}%")
            ->where('title', 'like', "%{$data['value']}%");
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereDate(Builder $query, array $data)
    {
        if (! is_null($data['start_date']) && ! is_null($data['end_date'])) {
            return $query->whereBetween('publish_time', [$data['start_date'], $data['end_date']]);
        } elseif (! is_null($data['start_date'])) {
            return $query->where('publish_time', '<=', "{$data['start_date']} 23:59:59");
        } elseif (! is_null($data['end_date'])) {
            return $query->where('publish_time', '>=', "{$data['end_date']} 00:00:00");
        }

        return $query;
    }
}
