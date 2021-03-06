<?php

namespace App\Repositories;

use App\Models\Profit\Revenue;
use Illuminate\Support\Facades\DB;

class RevenueRepository extends Repository
{
    /**
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(int $year, int $month)
    {
        return Revenue::query()->select(
            'year',
            'month',
            'value',
            DB::RAW("ROUND(qoq, 2) AS qoq"),
            DB::RAW("ROUND(yoy, 2) AS yoy"),
            'stocks.code',
            'stocks.name',
            DB::RAW('classifications.name as cname'),
            'total',
            'y_total',
            'total_increase',
        )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id')
            ->where('year', $year)
            ->where('month', $month)
            ->orderByDesc('yoy')
            ->get();
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function gets(string $code, int $year)
    {
        return Revenue::query()
            ->select(
                DB::RAW('revenues.*')
            )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->where('stocks.code', $code)
            ->where('revenues.year', $year)
            ->get();
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function recent(string $code, int $year, int $month)
    {
        return Revenue::query()
            ->select(
                DB::RAW('`revenues`.`year`'),
                DB::RAW('`revenues`.`month`'),
                DB::RAW('ROUND(revenues.yoy, 2) as yoy'),
                DB::RAW('ROUND(revenues.qoq, 2) as qoq'),
                DB::RAW('`revenues`.`value`'),
                DB::RAW('`revenues`.`total`'),
                DB::RAW('`revenues`.`y_total`'),
                DB::RAW('ROUND(revenues.total_increase, 2) as total_increase'),
            )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->where('stocks.code', $code)
            ->where('revenues.year', '<=', $year)
            ->orderByDesc('revenues.year')
            ->orderByDesc('revenues.month')
            ->limit(72)
            ->get()->filter(function ($v) use ($year, $month) {
                return ($year == $v->year) ? $v->month <= $month : true;
            })->values();
    }

    /**
     * @param array $codes
     * @param int $year
     * @param int $month
     *
     * @return array
     */
    public function recents(array $codes, int $year, int $month)
    {
        $revenues = Revenue::query()
            ->select(
                DB::RAW('`stocks`.`code`'),
                DB::RAW('`revenues`.`year`'),
                DB::RAW('`revenues`.`month`'),
                DB::RAW('`revenues`.`value`'),
            )->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->whereIn('stocks.code', $codes)
            ->where('revenues.year', '<=', $year)
            ->where('revenues.year', '>=', $year - 5)
            ->orderByDesc('revenues.year')
            ->orderByDesc('revenues.month')
            ->get();

        return $revenues->groupBy('code')->map(function ($revenue, $code) {
            $revenue->map(function ($v, $i) use ($revenue) {
                $ye = $revenue->where('year', $v->year - 1)
                    ->where('month', $v->month)
                    ->first();

                $v->yoy = is_null($ye) ? 0 : round((($v->value / $ye->value) - 1) * 100, 2);
                $v->qoq = isset($revenue[$i + 1]) ? round((($v->value / $revenue[$i + 1]->value) - 1) * 100, 2) : 0;

                return $v;
            });

            return $revenue;
        });
    }


    /**
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function download(int $year, int $month)
    {
        return Revenue::query()
            ->select(
                'code', 'stocks.name', DB::RAW('classifications.name as c_name'),
                'value', 'yoy', 'qoq', 'total', 'y_total', 'total_increase'
            )->join('stocks', 'revenues.stock_id', '=', 'stocks.id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id')
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->map(function ($value) {
                return [
                    '??????' => $value['code'],
                    '??????' => $value['name'],
                    '??????(???)' => number_format($value['value']),
                    'yoy' => $value['yoy'],
                    'qoq' => $value['qoq'],
                    '????????????(???)' => $value['total'],
                    '??????????????????(???)' => $value['y_total'],
                    '????????????' => $value['total_increase'],
                    '????????????' => $value['c_name'],
                ];
            });
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all(string $code)
    {
        return Revenue::query()->select(DB::RAW('revenues.*'))->join('stocks', 'revenues.stock_id', '=', 'stocks.id')
            ->where('code', $code)
            ->where('year', '>=', date('Y') - 4)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    /**
     * @param int $year
     * @param int $month
     * @param array $codes
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function last(int $year, int $month, array $codes)
    {
        return Revenue::query()
            ->select('stocks.code', 'stocks.name', 'value', 'yoy', 'qoq', 'total', 'total_increase', 'y_total')
            ->join('stocks', 'stocks.id', '=', 'revenues.stock_id')
            ->where('year', $year)
            ->where('month', $month)
            ->whereIn('stocks.code', $codes)
            ->get();
    }
}
