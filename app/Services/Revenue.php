<?php

namespace App\Services;

use App\Repositories\RevenueRepository;
use Illuminate\Http\Request;

class Revenue
{
    /**
     * @var RevenueRepository
     */
    private RevenueRepository $repo;

    /**
     * Revenue constructor.
     *
     * @param RevenueRepository $repo
     */
    public function __construct(RevenueRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function gets(string $code, int $year)
    {
        return $this->repo->gets($code, $year);
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
        return $this->repo->recent($code, $year, $month);
    }

    /**
     * @param array $codes
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function recents(array $codes, int $year, int $month)
    {
        $data = [];
        foreach ($this->repo->recents($codes, $year, $month) as $value) {
            $data = array_merge($data, $value->toArray());
        }

        $data = collect($data)->groupBy('year');

        if ($data->count() > 0) {
            $data = $data->slice(0, -1);
        }

        $revenues = [];

        foreach ($data as $year => $value) {
            for ($i = 12; $i > 0; $i--) {
                $v = $value->where('month', $i);

                if ($v->count() == 0) {
                    continue;
                }

                $tmp = [];
                foreach ($codes as $code) {
                    $revenue = $v->where('code', $code)->first();

                    if (is_null($revenue)) {
                        $tmp[] = [
                            'code' => $code,
                            'year' => $year,
                            'month' => $i,
                            'value' => 0,
                            'yoy' => 0,
                            'qoq' => 0,
                        ];
                    } else {
                        $tmp[] = $revenue;
                    }
                }

                $revenues[] = $tmp;
            }
        }

        return $revenues;
    }
}
