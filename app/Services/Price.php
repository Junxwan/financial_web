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
        if (count($prices['data']) > 0) {
            $yPrices = $this->repo->yesterdayListById(
                $prices['data']->pluck('id')->toArray(), $prices['data'][0]['date']
            );

            $prices['data']->map(function ($item) use ($yPrices) {
                $item->y_volume_b = round($item->volume / $yPrices->where('stock_id', $item->id)->first()->volume, 1);
                return $item;
            });
        }

        return $prices;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public function price(string $code)
    {
        $price = $this->repo->stock($code, date('Y'));
        $data = [
            'price' => [],
            'volume' => [],
        ];

        foreach ($price as $value) {
            $p = $value->only(['open', 'close', 'increase', 'high', 'low']);
            $p['x'] = strtotime($value->date) * 1000;

            $data['price'][] = $p;
            $data['volume'][] = [
                'x' => $p['x'],
                'y' => $value->volume,
            ];
        }

        $data['name'] = \App\Models\Stock::query()->select('name')->where('code', $code)->first()->name;
        return $data;
    }
}
