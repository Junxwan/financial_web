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
        $prices = $this->repo->list($data);
        $yPrices = $this->repo->yesterdayListById(
            $prices['data']->pluck('id')->toArray(), $prices['data'][0]['date']
        );

        $prices['data']->map(function ($item) use ($yPrices) {
            $item->y_volume_b = round($item->volume / $yPrices->where('stock_id', $item->id)->first()->volume, 1);
            return $item;
        });

        return $prices;
    }
}
