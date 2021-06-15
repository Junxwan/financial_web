<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class Fund
{
    /**
     * @return array
     */
    public function years()
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 0; $i < ($now->year - 2012); $i++) {
            $data[] = $now->year - $i;

        }

        return $data;
    }
}
