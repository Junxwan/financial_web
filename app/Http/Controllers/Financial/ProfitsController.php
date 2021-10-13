<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Repositories\ProfitRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfitsController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.profit.compare';

    /**
     * @param Request $request
     *
     * @return array[]
     */
    protected function initView(Request $request)
    {
        $now = Carbon::now();

        $quarterly = 4;
        if ($now->month >= 11) {
            $quarterly = 3;
        } elseif ($now->month >= 8) {
            $quarterly = 2;
        } elseif ($now->month >= 5) {
            $quarterly = 1;
        }

        return [
            'year' => $now->year,
            'month' => $now->month - 1,
            'quarterly' => $quarterly,
            'tags' => Tag::query()->get(),
        ];
    }

    /**
     * @param Request $request
     * @param ProfitRepository $repo
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quarterly(Request $request, ProfitRepository $repo, int $year, int $quarterly)
    {
        return response()->json(
            $repo->quarterly($year, $quarterly, explode(',', $request->query('code')))
        );
    }
}
