<?php

namespace App\Http\Controllers;

use App\Services\Industry;
use Illuminate\Http\Request;

class IndustryController
{
    /**
     * @var Industry
     */
    private Industry $industry;

    /**
     * IndustryController constructor.
     *
     * @param Industry $industry
     */
    public function __construct(Industry $industry)
    {
        $this->industry = $industry;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.industry', [
            'header' => [
                '代碼',
                '名稱',
                '漲幅',
                '成交占比',
                '成交值',
            ],
            'modal' => [],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $data = $this->industry->list($request->all());
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
        ]);
    }
}
