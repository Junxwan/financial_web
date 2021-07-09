<?php

namespace App\Http\Controllers;

use App\Services\Category;
use Illuminate\Http\Request;

class CategoryController
{
    /**
     * @var Category
     */
    private Category $category;

    /**
     * IndustryController constructor.
     *
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.category', [
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
        $data = $this->category->list($request->all());
        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['total'],
            'data' => $data['data'],
        ]);
    }
}
