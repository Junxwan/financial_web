<?php

namespace App\Http\Controllers;

use App\Models\Stock\Population;
use App\Models\Stock\Price;
use App\Services\Industry;

class IndustryController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.industry';

    /**
     * @var string[]
     */
    protected $header = [
        '名稱',
        '漲幅',
        '成交值',
        '檔數',
    ];

    /**
     * IndustryController constructor.
     *
     * @param Industry $industry
     */
    public function __construct(Industry $industry)
    {
        $this->service = $industry;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function date()
    {
        return response()->json([
            'date' => Price::query()
                ->select('date')
                ->where('stock_id', Population::query()->select('stock_id')->limit(1)->first()->stock_id)
                ->orderByDesc('date')
                ->limit(1)
                ->first()
                ->date,
        ]);
    }
}
