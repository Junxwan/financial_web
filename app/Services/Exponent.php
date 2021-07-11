<?php

namespace App\Services;

use App\Repositories\PriceRepository;
use App\Repositories\ProfitRepository;
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
     * @var ProfitRepository
     */
    private ProfitRepository $profit;

    /**
     * Exponent constructor.
     *
     * @param ProfitRepository $profit
     * @param PriceRepository $price
     * @param TagRepository $tag
     */
    public function __construct(ProfitRepository $profit, PriceRepository $price, TagRepository $tag)
    {
        $this->tag = $tag;
        $this->price = $price;
        $this->profit = $profit;
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

    public function tagProfit(int $tag, int $year, int $quarterly)
    {
        return $this->profit->codeByTag($tag, $year, $quarterly);
    }
}
