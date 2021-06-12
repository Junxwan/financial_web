<?php

use Illuminate\Database\Eloquent\Collection;

if (! function_exists('q4r')) {
    /**
     * @param Collection $data
     * @param $key
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    function q4r(Collection $data, $key)
    {
        $update = [];
        foreach ($data->groupBy('year') as $year => $value) {
            if ($value->count() == 4) {
                if (! is_array($key)) {
                    $key = [$key];
                }

                $q123 = $data->where('year', $year)->where('quarterly', '<=', 3);
                $q4 = $data->where('year', $year)->where('quarterly', '=', 4)->first();
                $update[$year] = [];

                foreach ($key as $k) {
                    $update[$year][$k] = $q4->{$k} - $q123->sum($k);
                }
            }
        }

        if (count($update) == 0) {
            return $data;
        }

        return $data->map(function ($v) use ($update) {
            if ($v->quarterly == 4 && isset($update[$v->year]) && count($update[$v->year]) > 0) {
                foreach ($update[$v->year] as $key => $value) {
                    $v->{$key} = $update[$v->year][$key];
                }

                return $v;
            }

            return $v;
        });
    }
}


