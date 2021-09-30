<?php

namespace App\Services;

use App\Models\Classification;
use App\Models\Dividend;
use App\Models\Price;
use App\Models\Profit as Model;
use App\Models\StockTag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Profit
{
    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function year(string $code, int $year)
    {
        $data = Model::query()->select(
            DB::RAW('profits.*')
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', $year)
            ->get();

        return q4r($data, [
            'revenue',
            'gross',
            'fee',
            'outside',
            'other',
            'profit',
            'profit_pre',
            'profit_after',
            'tax',
            'profit_non',
            'profit_main',
            'eps',
        ]);
    }

    /**
     * 近四與三季eps總和
     *
     * @param int $codeId
     *
     * @return array
     */
    public function epsSum(int $codeId)
    {
        $profit = Model::query()
            ->select('year', 'quarterly', 'eps')
            ->where('stock_id', $codeId)
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->limit(5)
            ->get();

        $profit = q4r($profit, 'eps');
        $eps4Sum = round($profit->slice(0, 4)->sum('eps'), 2);

        if ($profit->count() < 4) {
            $eps3Sum = $eps4Sum;
        } else {
            $eps3Sum = round($profit->slice(0, 3)->sum('eps'), 2);
        }

        return [$eps4Sum, $eps3Sum];
    }

    /**
     * @return array
     */
    public function quarterlys()
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 0; $i < ($now->year - 2012); $i++) {
            $year = ($now->year - $i);
            $l = 4;

            if ($i == 0) {
                if ($now->month >= 11) {
                    $l = 3;
                } elseif ($now->month >= 8) {
                    $l = 2;
                } elseif ($now->month >= 5) {
                    $l = 1;
                }
            }

            $data[$year] = $l;
        }

        $s = [];

        foreach ($data as $k => $v) {
            for ($i = $v; $i >= 1; $i--) {
                $s[] = "{$k}-Q{$i}";
            }
        }

        return $s;
    }

    /**
     * @return array
     */
    public function yearMonths()
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 0; $i < ($now->year - 2012); $i++) {
            $year = ($now->year - $i);
            $l = 12;

            if ($i == 0) {
                $l = $now->month - 1;
            }

            $data[$year] = $l;
        }

        $s = [];

        foreach ($data as $k => $v) {
            for ($i = $v; $i >= 1; $i--) {
                $s[] = "{$k}-" . sprintf("%02d", $i);
            }
        }

        return $s;
    }

    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function recent(string $code, int $year, int $quarterly)
    {
        $profit = Model::query()->select(
            DB::RAW('profits.year'),
            DB::RAW('profits.quarterly'),
            DB::RAW('profits.revenue'),
            DB::RAW('profits.cost'),
            DB::RAW('profits.gross'),
            DB::RAW('profits.fee'),
            DB::RAW('profits.outside'),
            DB::RAW('profits.other'),
            DB::RAW('profits.profit'),
            DB::RAW('profits.tax'),
            DB::RAW('profits.profit_pre'),
            DB::RAW('profits.profit_after'),
            DB::RAW('profits.profit_main'),
            DB::RAW('profits.profit_non'),
            DB::RAW('profits.research'),
            DB::RAW('profits.eps'),
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', '<=', $year)
            ->orderByDesc('profits.year')
            ->orderByDesc('profits.quarterly')
            ->limit(28)
            ->get()->filter(function ($v) use ($year, $quarterly) {
                return ($year == $v->year) ? $v->quarterly <= $quarterly : true;
            });

        $profit = q4r($profit, [
            'eps',
            'revenue',
            'cost',
            'gross',
            'fee',
            'outside',
            'other',
            'profit',
            'tax',
            'profit_pre',
            'profit_after',
            'profit_main',
            'profit_non',
            'research',
        ]);

        return $profit->map(function ($v) use ($profit) {
            $ye = $profit->where('year', $v->year - 1)
                ->where('quarterly', $v->quarterly)
                ->first();

            if (is_null($ye) || $ye->revenue == 0) {
                $v->revenue_yoy = 0;
            } else {
                $v->revenue_yoy = round((($v->revenue / $ye->revenue) - 1) * 100, 2);
            }

            $v->eps = round($v->eps, 2);

            return $v;
        });
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function eps(string $code)
    {
        $data = Model::query()->select(
            DB::RAW('profits.year'),
            DB::RAW('profits.eps'),
            DB::RAW('profits.quarterly')
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', '>=', Carbon::now()->year - 8)
            ->orderByDesc('profits.year')
            ->get()->groupBy('year');

        $eps = [];
        foreach ($data as $year => $value) {
            $q4 = $value->where('quarterly', 4)->first();

            $t = [
                'year' => $year,
                'eps' => is_null($q4) ? "" : $q4->eps,
            ];

            if (! is_null($q4)) {
                foreach (q4r($value, 'eps') as $v) {
                    $t["q{$v->quarterly}"] = round($v->eps, 2);
                }
            } else {
                for ($i = 1; $i <= 4; $i++) {
                    if (! is_null($v = $value->where('quarterly', $i)->first())) {
                        $t["q{$i}"] = round($v->eps, 2);
                    } else {
                        $t["q{$i}"] = "";
                    }
                }
            }

            $eps[] = $t;
        }

        return $eps;
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function dividend(string $code)
    {
        return Dividend::query()->select(
            DB::RAW('dividends.year'),
            DB::RAW('dividends.cash'),
        )->join('stocks', 'stocks.id', '=', 'dividends.stock_id')
            ->where('stocks.code', $code)
            ->orderByDesc('dividends.year')
            ->limit(8)
            ->get();
    }

    /**
     * @param int $year
     * @param int $quarterly
     * @param string $name
     * @param string $order
     * @param array $options
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function rank(int $year, int $quarterly, string $name, string $order, array $options)
    {
        $query = Model::query()->select('code', 'stocks.name', DB::RAW('classifications.name as cName'))
            ->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->join('classifications', 'classifications.id', '=', 'stocks.classification_id');

        switch ($name) {
            case 'gross':
            case 'profit':
            case 'profit_after':
                $query = $query->addSelect(DB::RAW('ROUND((' . $name . '/revenue)*100, 2) AS value'));
                break;
            case 'outside':
                $query = $query->addSelect(DB::RAW('ROUND((outside/profit_after)*100, 2) AS value'));
                break;
            case 'eps':
                $query = $query->addSelect(DB::RAW('ROUND(eps, 2) AS value'));
                break;
            case 'revenue':
                $query = $query->addSelect(DB::RAW('revenue AS value'));
                break;
        }

        $query = $query->where('year', $year)
            ->where('quarterly', $quarterly);

        $data = $query
            ->orderBy('value', $order)
            ->offset($options['start'])
            ->limit($options['limit'])
            ->get();

        $tags = StockTag::query()->select(
            DB::RAW('stock_tags.stock_id'),
            DB::RAW('tags.id'),
            DB::RAW('tags.name')
        )->join('tags', 'tags.id', '=', 'stock_tags.tag_id')
            ->whereIn('stock_tags.stock_id', $data->pluck('id'))
            ->get();

        return [
            'data' => $data->map(function ($value) use ($tags) {
                $t = [];
                foreach ($tags as $v) {
                    if ($value->id == $v->stock_id) {
                        $t[] = [
                            'id' => $v->id,
                            'name' => $v->name,
                        ];
                    }
                }

                $value->tags = $t;
                return $value;
            }),
            'total' => $query->count(),
        ];
    }

    /**
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function download(int $year, int $quarterly)
    {
        $classification = [];
        foreach (Classification::query()->get() as $value) {
            $classification[$value->id] = $value->name;
        }

        $all = Model::query()->select(
            'stocks.code',
            'stocks.name',
            'stocks.classification_id',
            'profits.year',
            'profits.quarterly',
            'profits.revenue',
            'profits.cost',
            'profits.gross',
            'profits.fee',
            'profits.profit',
            'profits.outside',
            'profits.other',
            'profits.profit_pre',
            'profits.profit_after',
            'profits.tax',
            'profits.eps',
        )->join('stocks', 'profits.stock_id', '=', 'stocks.id')
            ->whereIn('year', [$year, $year - 1])
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->get();

        $data = $all->groupBy('year');
        $cData = $data[$year]->groupBy('code');
        $cyData = $data[$year - 1]->groupBy('code');

        $date = Price::query()
            ->select('date')
            ->orderByDesc('date')
            ->limit(1)
            ->first()->date;

        $prices = Price::query()
            ->select(
                'stocks.code',
                'prices.close'
            )->join('stocks', 'prices.stock_id', '=', 'stocks.id')
            ->where('date', $date)
            ->get();

        return $data[$year]->where('quarterly', $quarterly)->map(function ($value) use (
            $year,
            $quarterly,
            $cData,
            $cyData,
            $classification,
            $date,
            $prices
        ) {
            $yGross = 0;
            $revenueYoy = 0;
            $ysRevenue = 0;
            $eps = 0;
            $yEps = 0;
            $close = 0;
            $pe = 0;

            // 去年
            if (isset($cyData[$value['code']])) {

                // 去年營收/eps
                if ($quarterly == 4) {
                    $ysRevenue = $cyData[$value['code']]['revenue'];
                    $yEps = $cyData[$value['code']]['eps'];
                } else {
                    $ysRevenue = $cyData[$value['code']]->sum('revenue');
                    $yEps = $cyData[$value['code']]->sum('eps');
                }

                $yData = $cyData[$value['code']]->where('quarterly', $quarterly)->first();

                if (! is_null($yData)) {
                    // 去年毛利
                    $yGross = round(($yData['gross'] / $yData['revenue']) * 100, 2);

                    // 今年營收yoy
                    $revenueYoy = round((($value['revenue'] / $yData['revenue']) - 1) * 100, 2);
                }
            }

            // 今年
            $v = $cData[$value['code']];

            $gross = round(($value['gross'] / $value['revenue']) * 100, 2);

            if ($quarterly == 4) {
                $ysGross = $gross;
                $sRevenue = $value['revenue'];
                $ysEps = $value['eps'];
                $eps = $value['eps'] - $v->where('quarterly', '!=', 4)->sum('eps');
            } else {
                // 今年累積毛利
                $ysGross = round(($v->sum('gross') / $v->sum('revenue')) * 100, 2);

                // 今年累積營收
                $sRevenue = $v->sum('revenue');

                // 今年累積eps
                $ysEps = $v->sum('eps');

                $eps = $value['eps'];
            }

            if (! is_null($p = $prices->where('code', $value['code'])->first())) {
                $close = $p->close;
                $e = 0;

                if ($quarterly == 4) {
                    $e = $value['eps'];
                } elseif (isset($cData[$value['code']]) && isset($cyData[$value['code']])) {
                    $all = collect(array_merge($cData[$value['code']]->toArray(), $cyData[$value['code']]->toArray()));

                    if ($all->count() >= 4) {
                        $e = $all->slice(0, 4)->sum('eps');
                    }
                }

                if ($e != 0) {
                    $pe = round($close / $e);
                }
            }

            $sq = "{$year}-Q{$quarterly}";

            return [
                'code' => $value['code'],
                'name' => $value['name'],
                "{$year} {$date} 收盤價" => $close,
                '近四季PE' => $pe,
                "{$sq}-毛利%" => $gross,
                "{$sq}-累積毛利%" => $ysGross,
                "去年-毛利%" => $yGross,
                "{$sq}-營收yoy" => $revenueYoy,
                "{$sq}-累積營收" => number_format($sRevenue),
                "去年-營收" => number_format($ysRevenue),
                "{$sq}-eps" => $eps,
                "{$sq}-累積eps" => $ysEps,
                "去年-eps" => $yEps,
                '產業分類' => $classification[$value['classification_id']],
            ];
        });
    }
}
