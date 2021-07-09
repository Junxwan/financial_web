<?php


namespace App\Services;

use App\Repositories\StockRepository;

class Stock
{
    /**
     * @var StockRepository
     */
    private StockRepository $repo;

    /**
     * IndexController constructor.
     *
     * @param StockRepository $repo
     */
    public function __construct(StockRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function priceList(array $data)
    {
        return $this->repo->priceList($data);
    }
}
