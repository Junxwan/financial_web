<?php

namespace App\Http\Controllers\Cb;

use App\Http\Controllers\Controller;
use App\Repositories\Cb\BalanceRepository;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.cb.balance';

    /**
     * @var string[]
     */
    protected $header = [
        '代碼',
        '名稱',
        '年',
        '月',
        '轉換張數',
        '剩餘張數',
        '轉換股數',
        '未登記股數',
        '剩餘比例',
    ];

    /**
     * IndexController constructor.
     *
     * @param BalanceRepository $repo
     */
    public function __construct(BalanceRepository $repo)
    {
        $this->service = $repo;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $data = $this->service->list($request->all());
        return response()->json([
            'data' => $data['data'],
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
        ]);
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(string $code)
    {
        return response()->json($this->service->get($code));
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function securitiesLendingRepay(string $code)
    {
        return response()->json($this->service->securitiesLendingRepay($code));
    }
}
