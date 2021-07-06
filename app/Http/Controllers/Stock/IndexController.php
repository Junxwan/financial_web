<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Classification;
use App\Repositories\StockRepository;
use App\Repositories\TagRepository;
use App\Services\Profit as Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.stock.index';

    /**
     * @var string[]
     */
    protected $header = [
        '代碼',
        '名稱',
        '產業',
        '市場',
        '標籤',
        '編輯',
        '刪除',
    ];

    /**
     * IndexController constructor.
     *
     * @param StockRepository $repo
     */
    public function __construct(StockRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function initView(Request $request)
    {
        $ct = Classification::query()->select(DB::raw('id as value'), 'name')->get();
        $tags = app(TagRepository::class)->all();
        $o = new \stdClass();
        $o->name = '上櫃';
        $o->value = 2;

        $t = new \stdClass();
        $t->name = '上市';
        $t->value = 1;

        return [
            'classification' => $ct,
            'tags' => $tags,
            'market' => [$t, $o],
        ];
    }

    /**
     * @param array $data
     *
     * @return array[]
     */
    protected function createColumns(array $data)
    {
        return [
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
                'value' => $data['classification'],
            ],
            [
                'id' => 'market',
                'type' => 'select',
                'name' => '市場',
                'value' => $data['market'],
            ],
            [
                'id' => 'tag',
                'type' => 'duallistbox',
                'name' => '標籤',
                'value' => $data['tags'],
            ],
        ];
    }

    /**
     * @param Service $profit
     * @param string $code
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function search(Service $profit, string $code)
    {
        if (is_null($data = $this->repo->search($code))) {
            return response('', Response::HTTP_NOT_FOUND);
        }

        [$eps4, $eps3] = $profit->epsSum($data->id);
        $data['eps4_sum'] = $eps4;
        $data['eps3_sum'] = $eps3;

        return response()->json($data);
    }
}
