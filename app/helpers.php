<?php

use Illuminate\Database\Eloquent\Collection;

if (! function_exists('q4r')) {
    /**
     * @param Collection $data
     * @param $key
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    function q4r(Collection $data, $key)
    {
        $update = [];
        foreach ($data->groupBy('year') as $year => $value) {
            if ($value->count() == 4) {
                $q123 = $data->where('year', $year)->where('season', '<=', 3)->sum($key);
                $q4 = $data->where('year', $year)->where('season', '=', 4)->first()->{$key};
                $update[$year] = $q4 - $q123;
            }
        }

        if (count($update) == 0) {
            return $data;
        }

        return $data->map(function ($v) use ($update, $key) {
            if ($v->season == 4 && isset($update[$v->year])) {
                $v->{$key} = $update[$v->year];
                return $v;
            }

            return $v;
        });
    }
}


