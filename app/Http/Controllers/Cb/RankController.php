<?php

namespace App\Http\Controllers\Cb;

use App\Http\Controllers\Controller;
use App\Repositories\Cb\RankRepository;
use Illuminate\Http\Request;

class RankController
{
    /**
     * @var string
     */
    protected $view = 'page.cb.rank';

    /**
     * @var RankRepository
     */
    private RankRepository $repo;

    /**
     * RankController constructor.
     *
     * @param RankRepository $repo
     */
    public function __construct(RankRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view($this->view);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json($this->repo->list($request->all()));
    }
}
