<?php

namespace App\Repositories;

use App\Models\Profit\Equity;

class EquityRepository extends Repository
{
    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(int $id)
    {
        return Equity::query()
            ->where('stock_id', $id)
            ->orderByDesc('year')
            ->orderByDesc('quarterly')
            ->first();
    }
}
