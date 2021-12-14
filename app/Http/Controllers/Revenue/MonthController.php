<?php

namespace App\Http\Controllers\Revenue;

use App\Models\Stock\Classification;
use App\Repositories\RevenueRepository;
use App\Services\Revenue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthController
{
    /**
     * @var string
     */
    private $view = 'page.month_revenues';

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $now = Carbon::now();
        return view($this->view, [
            'year' => $now->year,
            'month' => $now->month - 1,
            'classification' => Classification::query()->get(),
        ]);
    }

    /**
     * @param Request $request
     * @param RevenueRepository $revenue
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(Request $request, RevenueRepository $revenue)
    {
        $now = Carbon::now();
        return $revenue->list(
            $request->get('year', $now->year),
            $request->get('month', $now->month),
        );
    }

    /**
     * @param int $year
     * @param int $month
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(int $year, int $month, RevenueRepository $revenue)
    {
        return response()->stream(function () use ($year, $month, $revenue) {
            $data = $revenue->download($year, $month);

            $file = fopen('php://output', 'w');
            fputcsv($file, array_keys($data->first()));

            foreach ($data as $v) {
                fputcsv($file, array_values($v));
            }

            fclose($file);

        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=test.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ]);
    }

    /**
     * @param Request $request
     * @param RevenueRepository $revenue
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function last(Request $request, RevenueRepository $revenue, int $year, int $month)
    {
        return response()->json(
            $revenue->last($year, $month, explode(',', $request->get('code')))
        );
    }

    /**
     * @param string $code
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadAll(Revenue $revenue, string $code)
    {
        return response()->stream(function () use ($code, $revenue) {
            $data = $revenue->downloadAll($code);

            $file = fopen('php://output', 'w');
            fputcsv($file, ['年月', '營收', 'yoy', 'qoq']);

            foreach ($data as $v) {
                fputcsv($file, $v);
            }

            fclose($file);
        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=test.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ]);
    }
}
