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

        $_prices = Price::query()
            ->select(
                'stocks.code',
                'prices.close'
            )->join('stocks', 'prices.stock_id', '=', 'stocks.id')
            ->where('prices.date', '2021-07-28')
            ->get();

        $prices = [];
        foreach ($_prices as $value) {
            $prices[$value->code] = $value->close;
        }

        return $data[$year]->where('quarterly', $quarterly)->map(function ($value) use (
            $year,
            $quarterly,
            $cData,
            $cyData,
            $classification,
            $date,
            $prices
        ) {
            $v = isset($cData[$value['code']]) ? $cData[$value['code']] : collect();
            $yv = isset($cyData[$value['code']]) ? $cyData[$value['code']] : collect();
            $yq = $yv->where('quarterly', $quarterly)->first();
            $yqe = $yv->where('quarterly', 4)->first();

            // 收盤價
            $close = isset($prices[$value['code']]) ? $prices[$value['code']] : 0;

            // 該季eps
            $eps = $value['eps'];

            // 該年累積eps
            $epsTotal = $quarterly == 4 ? $eps : $v->sum('eps');

            // 去年eps
            if (is_null($yqe)) {
                $yEpsTotal = $yv->sum('eps');
            } else {
                $yEpsTotal = $yqe->eps;
            }

            // eps yoy
            if (! is_null($yq) && $yq->eps != 0) {
                $epsYoy = round(($value['eps'] / $yq->eps) * 100, 2);
            } else {
                $epsYoy = 0;
            }

            // 該季毛利%
            $gross = $value['revenue'] > 0 ? round(($value['gross'] / $value['revenue']) * 100, 2) : 0;

            // 去年同期毛利%
            if (! is_null($yq) && $yq['revenue'] > 0) {
                $yGross = round(($yq['gross'] / $yq['revenue']) * 100, 2);
            } else {
                $yGross = 0;
            }

            // 該季營收yoy
            if (! is_null($yq) && $value['revenue'] > 0 && $yq['revenue'] > 0) {
                $revenueYoy = round(($value['revenue'] / $yq['revenue']) * 100, 2);
            } else {
                $revenueYoy = 0;
            }

            // 今年累積營收
            $revenueTotal = $quarterly == 4 ? $value['revenue'] : $cData[$value['code']]->sum('revenue');

            // 去年營收
            if (is_null($yqe)) {
                $yRevenueTotal = $yv->sum('revenue');
            } else {
                $yRevenueTotal = $yqe->revenue;
            }

            // 近四季PE
            $pe = 0;
            if ($close > 0) {
                if ($quarterly == 4) {
                    $e = $value['eps'];
                } else {
                    $all = collect(array_merge($v->toArray(), $yv->toArray()));

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
                "去年-毛利%" => $yGross,
                "{$sq}-營收yoy" => $revenueYoy,
                "{$sq}-累積營收" => number_format($revenueTotal),
                "去年-營收" => number_format($yRevenueTotal),
                "{$sq}-eps" => $eps,
                "{$sq}-eps-yoy" => $epsYoy,
                "{$sq}-累積eps" => $epsTotal,
                "去年-eps" => $yEpsTotal,
                '產業分類' => $classification[$value['classification_id']],
            ];
        });
    }
}
