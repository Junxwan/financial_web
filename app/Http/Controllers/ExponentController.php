<?php

namespace App\Http\Controllers;

use App\Services\Exponent;
use Illuminate\Http\Request;

class ExponentController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.exponent';

    /**
     * @var Exponent
     */
    private Exponent $exponent;

    /**
     * ExponentController constructor.
     *
     * @param Exponent $exponent
     */
    public function __construct(Exponent $exponent)
    {
        $this->exponent = $exponent;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function initView(Request $request)
    {
        return [
            'tags' => $this->exponent->tags(),
            'year' => date('Y'),
        ];
    }

    /**
     * @param int $tagId
     * @param int $year
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tag(int $tagId, int $year)
    {
        return response($this->exponent->tag($tagId, $year));
    }
}
