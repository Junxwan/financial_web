<?php

namespace App\Services;

use App\Repositories\FundRepository;
use Illuminate\Support\Carbon;

class Fund
{
    private FundRepository $repo;

    /**
     * Fund constructor.
     *
     * @param FundRepository $repo
     */
    public function __construct(FundRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return array
     */
    public function years()
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 0; $i < ($now->year - 2015); $i++) {
            $data[] = $now->year - $i;

        }

        return $data;
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByCompany(int $id)
    {
        return $this->repo->getByCompany($id);
    }

    /**
     * @param int $year
     * @param int $fundId
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function stocks(int $year, int $fundId)
    {
        return $this->repo->stocks($year, $fundId);
    }

    /**
     * @param string $code
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    public function stock(string $code, int $year)
    {
        return $this->repo->stock($code, $year);
    }
}
