<?php

namespace App\Repositories\Cb;

use App\Models\Cb\Balance;
use App\Models\Cb\Cb;

class BalanceRepository
{
    /**
     * @param int $id
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = Balance::query()
            ->select('cb_id', 'code', 'name', 'year', 'month', 'change', 'balance', 'change_stock', 'balance_stock')
            ->join('cbs', 'cbs.id', '=', 'cb_balances.cb_id');

        if (isset($data['search']) && isset($data['search']['value'])) {
            $query = $query
                ->where('code', $data['search']['value'])
                ->orderByDesc('year')
                ->orderByDesc('month');
        } else {
            $year = (int)date('Y');
            $month = (int)date('m') - 1;

            if ($month == 0) {
                $month = 12;
                $year -= 1;
            }

            $query = $query->where('year', $year)
                ->where('month', $month)
                ->orderBy('change');
        }

        $data = $query->offset($data['start'])
            ->limit($data['limit'])
            ->get();

        $cbs = Cb::query()
            ->select('id', 'name', 'publish_total_amount')
            ->whereIn('id', $data->pluck('cb_id'))
            ->get();

        return [
            'total' => $query->count(),
            'data' => $data->map(function ($value) use ($cbs) {
                $amount = $cbs->where('id', $value->cb_id)->first()->publish_total_amount;
                $value['balance_rate'] = round(($value['balance'] / ($amount / 100000)) * 100, 2);
                return $value;
            }),
        ];
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get(string $code)
    {
        $cb = Cb::query()
            ->select('name', 'publish_total_amount')
            ->where('code', $code)
            ->first();

        return [
            'name' => $cb->name,
            'data' => Balance::query()
                ->select('year', 'month', 'change', 'balance')
                ->join('cbs', 'cbs.id', '=', 'cb_balances.cb_id')
                ->where('code', $code)
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->get()
                ->map(function ($value) use ($cb) {
                    $value['balance_rate'] = round((($value->balance * 100000) / $cb->publish_total_amount) * 100);
                    return $value;
                }),
        ];
    }
}
