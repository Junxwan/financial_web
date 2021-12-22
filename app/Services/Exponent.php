<?php

namespace App\Services;

use App\Repositories\PopulationRepository;
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
     * @var PopulationRepository
     */
    private PopulationRepository $population;

    /**
     * Exponent constructor.
     *
     * @param StockRepository $stock
     * @param ProfitRepository $profit
     * @param PriceRepository $price
     * @param TagRepository $tag
     * @param PopulationRepository $population
     */
    public function __construct(
        StockRepository $stock,
        ProfitRepository $profit,
        PriceRepository $price,
        TagRepository $tag,
        PopulationRepository $population
    ) {
        $this->tag = $tag;
        $this->price = $price;
        $this->profit = $profit;
        $this->stock = $stock;
        $this->population = $population;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function populations()
    {
        return $this->population->all();
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return array[]
     */
    public function k(int $id, int $year)
    {
        $exponent = $this->price->population($id, $year);
        $data = [
            'price' => [],
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

        $data['name'] = $this->population->get($id)->name;
        $data['stock'] = $this->population->stock($id);
        return $data;
    }

    /**
     * @param int $id
     * @param int $year
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function stockKs(int $id, int $year)
    {
        $data = $this->price->stockByPopulation($id, $year);
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
     * @param int $id
     * @param int $year
     * @param int $quarterly
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function profit(int $id, int $year, int $quarterly)
    {
        return $this->profit->codeByPopulation($id, $year, $quarterly);
    }
}
