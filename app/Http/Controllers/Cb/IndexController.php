<?php

namespace App\Http\Controllers\Cb;

use App\Http\Controllers\Controller;
use App\Repositories\Cb\IndexRepository;

class IndexController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.cb.index';

    /**
     * @var string[]
     */
    protected $header = [
        '代碼',
        '名',
        '開始',
        '結束',
        '開始轉換',
        '轉換價',
        '轉換溢價率',
        '發行額',
        '擔保',
        '說明書',
    ];

    /**
     * IndexController constructor.
     *
     * @param IndexRepository $repo
     */
    public function __construct(IndexRepository $repo)
    {
        $this->service = $repo;
    }
}
