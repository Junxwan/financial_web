<?php

namespace App\Services;

use App\Repositories\PriceRepository;
use App\Repositories\ProfitRepository;
use App\Repositories\StockRepository;
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
     * @var StockRepository
     */
    private StockRepository $stock;

    /**
     * Exponent constructor.
     *
     * @param StockRepository $stock
     * @param ProfitRepository $profit
     * @param PriceRepository $price
     * @param TagRepository $tag
     */
    public function __construct(
        StockRepository $stock,
        ProfitRepository $profit,
        PriceRepository $price,
        TagRepository $tag
    ) {
        $this->tag = $tag;
        $this->price = $price;
        $this->profit = $profit;
        $this->stock = $stock;
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

            $data['price'][] = $p;
            $data['volume'][] = [
                'x' => $p['x'],
                'y' => $value->volume,
            ];
        }

        $data['name'] = $this->tag->get($id)->name;
        $data['stock'] = $this->tag->stockByTag($id);
        return $data;
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function stockK(int $id, int $year)
    {
        $data = $this->price->stockByTag($id, $year);
        return $this->stock->gets($data->keys()->toArray())->map(function ($value) use ($data) {
            $price = [];
            $volume = [];
            foreach ($data[$value->id] as $v) {
                $p = $v->only(['open', 'close', 'increase', 'high', 'low']);
                $p['x'] = strtotime($v->date) * 1000;

                $price[] = $p;
                $volume[] = [
                    'x' => $p['x'],
                    'y' => $v->volume,
                ];
            }

            return [
                'code' => $value->code,
                'name' => $value->name,
                'price' => $price,
                'volume' => $volume,
            ];
        })->values();
    }

    /**
     * @param int $tag
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function tagProfit(int $tag, int $year, int $quarterly)
    {
        return $this->profit->codeByTag($tag, $year, $quarterly);
    }
}
