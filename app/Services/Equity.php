<?php

namespace App\Services;

use App\Repositories\EquityRepository;

class Equity
{
    /**
     * @var EquityRepository
     */
    private EquityRepository $repo;

    /**
     * Equity constructor.
     *
     * @param EquityRepository $repo
     */
    public function __construct(EquityRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(int $id)
    {
        return $this->repo->get($id);
    }
}
