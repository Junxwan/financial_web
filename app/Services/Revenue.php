<?php

namespace App\Services;

use App\Repositories\RevenueRepository;

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

}
