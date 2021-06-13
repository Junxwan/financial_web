<?php

use Illuminate\Database\Eloquent\Collection;

if (! function_exists('q4r')) {
    /**
     * @param Collection $data
     * @param $key
     * @param bool $isAccumulate
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    function q4r(Collection $data, $key, $isAccumulate = false)
    {
        if (! is_array($key)) {
            $key = [$key];
        }

        $update = [];
        foreach ($data->groupBy('year') as $year => $value) {
            if ($value->count() == 4 && ! $isAccumulate) {
                $q123 = $data->where('year', $year)->where('quarterly', '<=', 3);
                $q4 = $data->where('year', $year)->where('quarterly', '=', 4)->first();
                $update[$year] = [];

                foreach ($key as $k) {
                    $update[$year][$k] = $q4->{$k} - $q123->sum($k);
                }
            } elseif ($isAccumulate) {
                foreach ($value as $v) {
                    if ($v->quarterly != 1) {
                        if (! is_null($item = $value->where('quarterly', $v->quarterly - 1)->first())) {
                            foreach ($key as $k) {
                                $update[$year][$v->quarterly][$k] = $v[$k] - $item[$k];
                            }
                        }
                    }
                }
            }
        }

        if (count($update) == 0) {
            return $data;
        }

        return $data->map(function ($v) use ($isAccumulate, $update) {
            if (! $isAccumulate && $v->quarterly == 4 && isset($update[$v->year]) && count($update[$v->year]) > 0) {
                foreach ($update[$v->year] as $key => $value) {
                    $v->{$key} = $update[$v->year][$key];
                }

                return $v;
            } elseif ($isAccumulate && isset($update[$v->year]) && isset($update[$v->year][$v->quarterly])) {
                foreach ($update[$v->year][$v->quarterly] as $key => $value) {
                    $v->{$key} = $value;
                }
            }

            return $v;
        });
    }
}


