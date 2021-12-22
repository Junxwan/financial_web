<?php

namespace App\Http\Controllers\Population;

use App\Http\Controllers\Controller;
use App\Services\Exponent;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.population.index';

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
            'tags' => $this->exponent->populations(),
            'year' => date('Y'),
        ];
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function exponent(int $id, int $year)
    {
        return response($this->exponent->k($id, $year));
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function stockKs(int $id, int $year)
    {
        return response($this->exponent->stockKs($id, $year));
    }

    /**
     * @param int $id
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function profit(int $id, int $year, int $quarterly)
    {
        return response($this->exponent->profit($id, $year, $quarterly));
    }
}
