<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Repositories\NewsKeyWordRepository;

class KeyWordController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.news.keyWord';

    /**
     * @var string[]
     */
    protected $header = ['名稱', '關鍵字', '編輯', '刪除'];

    /**
     * KeyWordController constructor.
     *
     * @param NewsKeyWordRepository $keyWordRepo
     */
    public function __construct(NewsKeyWordRepository $keyWordRepo)
    {
        $this->service = $keyWordRepo;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function createColumns(array $data)
    {
        return [
            [
                'id' => 'name',
                'type' => 'text',
                'name' => '名稱',
            ],
            [
                'id' => 'keys',
                'type' => 'text',
                'name' => '關鍵字',
            ],
        ];
    }

}
