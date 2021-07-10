<?php

namespace App\Services;

use App\Repositories\PriceRepository;
use App\Repositories\TagRepository;

class Exponent
{
    /**
     * @var TagRepository
     */
    private TagRepository $tag;

    /**
     * @var PriceRepository
     */
    private PriceRepository $price;

    /**
     * Exponent constructor.
     *
     * @param PriceRepository $price
     * @param TagRepository $tag
     */
    public function __construct(PriceRepository $price, TagRepository $tag)
    {
        $this->tag = $tag;
        $this->price = $price;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function tags()
    {
        return $this->tag->exponents();
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return array[]
     */
    public function tag(int $id, int $year)
    {
        $exponent = $this->price->exponentByTag($id, $year);
        $data = [
            'prices' => [],
            'volume' => [],
        ];

        foreach ($exponent as $value) {
            $p = $value->only(['open', 'close', 'increase', 'high', 'low']);
            $p['x'] = strtotime($value->date) * 1000;

            $data['prices'][] = $p;
            $data['volume'][] = [
                'x' => $p['x'],
                'y' => $value->volume,
            ];
        }

        $data['name'] = $this->tag->get($id)->name;
        $data['stock'] = $this->tag->stockByTag($id);
        return $data;
    }
}
