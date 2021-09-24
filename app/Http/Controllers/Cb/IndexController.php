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
        '開始/結束',
        '轉換價',
        '股價',
        '市價',
        '理論價',
        '折溢%',
        '轉換率',
        '股數',
        '金額',
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
