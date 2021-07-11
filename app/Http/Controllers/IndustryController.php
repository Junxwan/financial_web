<?php

namespace App\Http\Controllers;

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
        '代碼',
        '名稱',
        '漲幅',
        '成交值',
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
}
