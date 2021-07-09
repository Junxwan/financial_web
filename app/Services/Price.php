<?php

namespace App\Services;

use App\Repositories\PriceRepository;

class Price
{
    /**
     * @var PriceRepository
     */
    private PriceRepository $repo;

    /**
     * Price constructor.
     *
     * @param PriceRepository $repo
     */
    public function __construct(PriceRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        return $this->repo->list($data);
    }
}
