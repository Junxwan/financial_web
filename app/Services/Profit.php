<?php

namespace App\Services;

use App\Models\Stock\Classification;
use App\Models\Profit\Dividend;
use App\Models\Stock\Price;
use App\Models\Profit\Profit as Model;
use App\Models\Profit\Equity;
use App\Models\Stock\Tag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Profit
{
    /**
     * @param string $code
     * @param int $year
     * @param int $quarterly
     *
     * @return array
     */
    public function get(string $code, int $year, int $quarterly)
    {
        $profit = Model::query()
            ->select(DB::RAW('profits.*'))
            ->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('code', $code)
            ->where('year', $year)
            ->get();

        $data = [];
        $q123 = $profit->where('quarterly', '!=', 4);
        foreach ($profit->where('quarterly', $quarterly)->first()->toArray() as $k => $v) {
            switch ($k) {
                case 'revenue':
                case 'cost':
                case 'eps':
                    if ($quarterly == 4) {
                        $data[$k] = round($v - $q123->sum($k), 2);
                    } else {
                        $data[$k] = $v;
                    }

                    break;
                case 'gross':
                case 'fee':
                case 'profit':
                case 'outside':
                case 'other':
                case 'profit_pre':
                case 'profit_after':
                case 'profit_main':
                case 'profit_non':
                    if ($quarterly == 4) {
                        $data[$k] = $v - $q123->sum($k);
                    } else {
                        $data[$k] = $v;
                    }

                    $data[$k . '_ratio'] = round(($data[$k] / $data['revenue']) * 100, 2);
                    break;
                case 'tax':
                    if ($quarterly == 4) {
                        $data[$k] = $v - $q123->sum($k);
                    } else {
                        $data[$k] = $v;
                    }

                    $data[$k . '_ratio'] = round(($data[$k] / $data['profit_pre']) * 100, 2);
                    break;
            }
        }

        $data['value'] = '';

        return $data;
    }

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
            DB::RAW('round(profits.gross_ratio, 2) as gross_ratio'),
            DB::RAW('profits.fee'),
            DB::RAW('round(profits.fee_ratio, 2) as fee_ratio'),
            DB::RAW('profits.outside'),
            DB::RAW('profits.other'),
            DB::RAW('profits.profit'),
            DB::RAW('round(profits.profit_ratio, 2) as profit_ratio'),
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

        $profit = $profit->map(function ($v) use ($profit) {
            if ($v->quarterly == 4) {
                $p = $profit->where('year', $v->year)->where('quarterly', '<=', 3);
                $v->revenue -= $p->sum('revenue');
                $v->cost -= $p->sum('cost');
                $v->gross -= $p->sum('gross');
                $v->gross_ratio = round(($v->gross / $v->revenue) * 100, 2);
                $v->fee -= $p->sum('fee');
                $v->fee_ratio = round(($v->fee / $v->revenue) * 100, 2);
                $v->profit -= $p->sum('profit');
                $v->profit_ratio = round(($v->profit / $v->revenue) * 100, 2);
                $v->outside -= $p->sum('outside');
                $v->other -= $p->sum('other');
                $v->profit_pre -= $p->sum('profit_pre');
                $v->profit_after -= $p->sum('profit_after');
                $v->profit_main -= $p->sum('profit_main');
                $v->profit_non -= $p->sum('profit_non');
                $v->tax -= $p->sum('tax');
                $v->research -= $p->sum('research');
                $v->eps -= $p->sum('eps');
                $v->eps = round($v->eps, 2);
            }

            return $v;
        });

        return $profit->map(function ($v) use ($profit) {
            $ye = $profit->where('year', $v->year - 1)
                ->where('quarterly', $v->quarterly)
                ->first();

            if (is_null($ye) || $ye->revenue == 0) {
                $v->revenue_yoy = 0;
            } else {
                $v->revenue_yoy = round((($v->revenue / $ye->revenue) - 1) * 100, 2);
                $v->y_gross_ratio = round((($v->gross_ratio / $ye->gross_ratio) - 1) * 100, 2);
                $v->y_fee_ratio = round((($v->fee_ratio / $ye->fee_ratio) - 1) * 100, 2);

                if ($ye->profit_ratio < 0 && $v->profit_ratio > 0) {
                    $v->y_profit_ratio = round((((((-$ye->profit_ratio) * 2) + $v->profit_ratio) / (-$ye->profit_ratio)) - 1) * 100,
                        2);
                } else {
                    $v->y_profit_ratio = round((($v->profit_ratio / $ye->profit_ratio) - 1) * 100, 2);
                }

                if ($ye->eps < 0 && $v->eps > 0) {
                    $v->y_eps_ratio = round((((((-$ye->eps) * 2) + $v->eps) / (-$ye->eps)) - 1) * 100,
                        2);
                } else {
                    $v->y_eps_ratio = round((($v->eps / $ye->eps) - 1) * 100, 2);
                }
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
            ->get()
            ->groupBy('year');

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
     * @return array
     */
    public function pe(string $code)
    {
        $data = Model::query()->select(
            DB::RAW('profits.year'),
            DB::RAW('profits.quarterly'),
            DB::RAW('profits.eps'),
            DB::RAW('profits.gross_ratio as gross')
        )->join('stocks', 'stocks.id', '=', 'profits.stock_id')
            ->where('stocks.code', $code)
            ->where('profits.year', '>=', Carbon::now()->year - 4)
            ->orderByDesc('profits.year')
            ->orderByDesc('profits.quarterly')
            ->get();

        $eps = $data->map(function ($v) use ($data) {
            if ($v->quarterly == 4) {
                $v->eps = round(
                    $v->eps - $data->where('year', $v->year)->where('quarterly', '<=', 3)->sum('eps'),
                    2
                );
            }
            return $v;
        });

        $prices = Price::query()->select(
            DB::RAW('YEAR(date) as year'),
            DB::RAW('MONTH(date) as month'),
            DB::RAW('MAX(close) as max'),
            DB::RAW('MIN(close) as min'),
            DB::RAW('AVG(close) as avg'),
        )->join('stocks', 'stocks.id', '=', 'prices.stock_id')
            ->where('stocks.code', $code)
            ->where('date', '>=', Carbon::now()->year - 5 . '-01-01')
            ->groupBy([DB::RAW('YEAR(date)'), DB::RAW('MONTH(date)')])
            ->get();

        $pe = [];
        for ($i = 0; $i < count($eps); $i++) {
            $p = $prices->where('year', $eps[$i]->year);
            $e = $eps->slice($i, 4);

            if ($e->count() != 4) {
                break;
            }

            $e4 = round($e->sum('eps'), 2);

            switch ($eps[$i]->quarterly) {
                case 1:
                    $p = $p->whereBetween('month', [1, 3]);
                    break;
                case 2:
                    $p = $p->whereBetween('month', [4, 6]);
                    break;
                case 3:
                    $p = $p->whereBetween('month', [7, 9]);
                    break;
                case 4:
                    $p = $p->whereBetween('month', [10, 12]);
                    break;
            }

            $max = $p->max('max');
            $min = $p->min('min');
            $avg = round($p->avg('avg'), 2);

            $pe[] = [
                'year' => $eps[$i]->year,
                'quarterly' => $eps[$i]->quarterly,
                'eps' => $e4,
                'gross' => $eps[$i]->gross,
                'pes' => [
                    'max' => round($max / $e4, 1),
                    'min' => round($min / $e4, 1),
                    'avg' => round($avg / $e4, 1),
                ],
                'prices' => [
                    'max' => $max,
                    'min' => $min,
                    'avg' => $avg,
                ],
            ];
        }

        return $pe;
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

        $tags = Tag::query()->select(
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

    /**
     * @param int $year
     * @param int $quarterly
     *
     * @return array
     */
    public function downloadV2(int $year, int $quarterly)
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
            'profits.gross_ratio',
            'profits.fee',
            'profits.fee_ratio',
            'profits.profit',
            'profits.profit_ratio',
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
        $now = $data[$year]->groupBy('code');
        $ye = $data[$year - 1]->groupBy('code');

        $data = [];
        foreach ($now as $code => $value) {
            try {
                $value = $value->where('quarterly', $quarterly)->first();

                if (is_null($value)) {
                    continue;
                }

                $grossRatio = $value->gross_ratio;
                $feeRatio = $value->fee_ratio;
                $profitRatio = $value->profit_ratio;
                $revenueYoy = 0;
                $grossYoy = 0;
                $feeYoy = 0;
                $profitYoy = 0;

                if ($value->revenue == 0) {
                    continue;
                }

                $v = $ye[$code]->where('quarterly', $quarterly)->first();

                if ($quarterly == 4) {
                    $p = $now[$code]->where('quarterly', '<=', 3);
                    $value->revenue -= $p->sum('revenue');
                    $value->gross -= $p->sum('gross');
                    $value->fee -= $p->sum('fee');
                    $value->profit -= $p->sum('profit');
                    $value->eps -= $p->sum('eps');
                    $value->outside -= $p->sum('outside');
                    $value->other -= $p->sum('other');
                    $value->tax -= $p->sum('tax');
                    $value->profit_pre -= $p->sum('profit_pre');
                    $value->profit_after -= $p->sum('profit_after');

                    $grossRatio = round(($value->gross / $value->revenue) * 100, 2);
                    $feeRatio = round(($value->fee / $value->revenue) * 100, 2);
                    $profitRatio = round(($value->profit / $value->revenue) * 100, 2);

                    $p = $ye[$code]->where('quarterly', '<=', 3);

                    if (! is_null($v)) {
                        $v->revenue -= $p->sum('revenue');
                        $v->gross -= $p->sum('gross');
                        $v->fee -= $p->sum('fee');
                        $v->profit -= $p->sum('profit');
                        $v->eps -= $p->sum('eps');

                        $revenueYoy = round((($value->revenue / $v->revenue) - 1) * 100, 2);
                        $grossYoy = round((($grossRatio / round(($v->gross / $v->revenue) * 100, 2)) - 1) * 100, 2);
                        $feeYoy = round((($feeRatio / round(($v->fee / $v->revenue) * 100, 2)) - 1) * 100, 2);

                        $pr = round(($v->profit / $v->revenue) * 100, 2);
                        if ($pr < 0) {
                            $profitYoy = round((($profitRatio + (-$pr) * 2 / (-$pr)) - 1) * 100, 2);
                        } else {
                            $profitYoy = round((($profitRatio / $pr) - 1) * 100, 2);
                        }
                    }
                } else {
                    if (! is_null($v)) {
                        $revenueYoy = round((($value->revenue / $v->revenue) - 1) * 100, 2);
                        $grossYoy = round((($value->gross_ratio / $v->gross_ratio) - 1) * 100, 2);
                        $feeYoy = round((($value->fee_ratio / $v->fee_ratio) - 1) * 100, 2);

                        if ($v->profit_ratio < 0) {
                            $profitYoy = round((($value->profit_ratio + (-$v->profit_ratio) * 2 / (-$v->profit_ratio)) - 1) * 100,
                                2);
                        } elseif ($v->profit_ratio == 0) {
                            $profitYoy = $value->profit_ratio * 100;
                        } else {
                            $profitYoy = round((($value->profit_ratio / $v->profit_ratio) - 1) * 100, 2);
                        }
                    }
                }

                if ($v->eps == 0) {
                    $epsYoy = $value->eps * 100;
                } elseif ($value->eps > 0 && $v->eps < 0) {
                    $epsYoy = round(((($value->eps + (-$v->eps) * 2) / (-$v->eps)) - 1) * 100, 2);
                } elseif ($value->eps < 0 && $v->eps < 0) {
                    $epsYoy = -round((($value->eps / $v->eps) - 1) * 100, 2);
                } elseif ($value->eps < 0 && $v->eps > 0) {
                    $epsYoy = -round(((((-$value->eps) + $v->eps * 2) / $v->eps) - 1) * 100, 2);
                } else {
                    $epsYoy = round((($value->eps / $v->eps) - 1) * 100, 2);
                }

                $data[] = [
                    'code' => $value->code,
                    'name' => $value->name,
                    '毛利率' => $grossRatio,
                    '費用率' => $feeRatio,
                    '利益率' => $profitRatio,
                    'eps' => $value->eps,
                    '營收年增' => $revenueYoy,
                    '毛利年增' => $grossYoy,
                    '費用年增' => $feeYoy,
                    '利益年增' => $profitYoy,
                    'eps年增' => $epsYoy,
                    '營收' => $value->revenue,
                    '稅前' => $value->profit_pre,
                    '稅後' => $value->profit_after,
                    '所得稅' => $value->tax,
                    '業外' => $value->outside,
                    '其他' => $value->other,
                    '產業分類' => $classification[$value['classification_id']],
                ];
            } catch (\ErrorException $e) {
                $data[] = [
                    'code' => $value->code,
                    'name' => $value->name,
                    '毛利率' => $grossRatio,
                    '費用率' => $feeRatio,
                    '利益率' => $profitRatio,
                    'eps' => $value->eps,
                    '營收年增' => $revenueYoy,
                    '毛利年增' => $grossYoy,
                    '費用年增' => $feeYoy,
                    '利益年增' => $profitYoy,
                    'eps年增' => $epsYoy,
                    '營收' => $value->revenue,
                    '稅前' => $value->profit_pre,
                    '稅後' => $value->profit_after,
                    '所得稅' => $value->tax,
                    '業外' => $value->outside,
                    '其他' => $value->other,
                    '產業分類' => $classification[$value['classification_id']],
                ];
            }
        }

        return $data;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public function downloadAll(string $code)
    {
        $column = [
            'revenue',
            'cost',
            'gross',
            'gross_ratio',
            'fee',
            'fee_ratio',
            'profit',
            'profit_ratio',
            'outside',
            'other',
            'profit_pre',
            'profit_after',
            'profit_main',
            'profit_non',
            'tax',
            'eps',
        ];

        $profit = Model::query()->select(array_merge(['year', 'quarterly'], $column),)
            ->join('stocks', 'profits.stock_id', '=', 'stocks.id')
            ->where('code', $code)
            ->where('year', '>=', date('Y') - 4)
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->get();

        $profit = $profit->map(function ($v) use ($profit) {
            if ($v->quarterly == 4) {
                $p = $profit->where('year', $v->year)->where('quarterly', '<=', 3);
                $v->revenue -= $p->sum('revenue');
                $v->cost -= $p->sum('cost');
                $v->gross -= $p->sum('gross');
                $v->gross_ratio = round(($v->gross / $v->revenue) * 100, 2);
                $v->fee -= $p->sum('fee');
                $v->fee_ratio = round(($v->fee / $v->revenue) * 100, 2);
                $v->profit -= $p->sum('profit');
                $v->profit_ratio = round(($v->profit / $v->revenue) * 100, 2);
                $v->outside -= $p->sum('outside');
                $v->other -= $p->sum('other');
                $v->profit_pre -= $p->sum('profit_pre');
                $v->profit_after -= $p->sum('profit_after');
                $v->profit_main -= $p->sum('profit_main');
                $v->profit_non -= $p->sum('profit_non');
                $v->tax -= $p->sum('tax');
                $v->eps -= $p->sum('eps');
                $v->eps = round($v->eps, 2);
            }

            return $v;
        });

        $data = [];
        foreach ($column as $value) {
            $data[] = array_merge([$value], $profit->pluck($value)->toArray());
        }

        $column = ['欄位'];
        foreach ($profit as $value) {
            $column[] = $value->year . '-Q' . $value->quarterly;
        }

        return [
            'column' => $column,
            'data' => $data,
        ];
    }
}
