<?php

namespace App\Services;

use App\Repositories\PriceRepository;
use App\Repositories\PopulationRepository;

class Industry
{
    /**
     * @var PriceRepository
     */
    private PriceRepository $price;

    /**
     * @var PopulationRepository
     */
    private PopulationRepository $tag;

    /**
     * Industry constructor.
     *
     * @param PriceRepository $price
     * @param PopulationRepository $tag
     */
    public function __construct(PriceRepository $price, PopulationRepository $tag)
    {
        $this->price = $price;
        $this->tag = $tag;
    }

    /**
     * @param array $select
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function list(array $select)
    {
        $date = null;
        $tag = $this->tag->all();

        if (isset($select['search'])) {
            $search = $select['search'];
            if (isset($search['start_date']) && ! is_null($search['start_date'])) {
                $date = $search['start_date'];
            }
        }

        $ids = $tag->pluck('id')->toArray();
        $count = $this->tag->count($ids);
        $price = $this->price->getByTag($ids, $date);

        $data = $tag->map(function ($item) use ($price, $count) {
            $item->increase = 0;
            $item->volume = 0;

            $increase = [];
            $value = [];
            foreach ($price as $v) {
                if (in_array($item->id, $v->tag_id)) {
                    $increase[] = $v->increase;
                    $value[] = $v->value;
                }
            }

            if (count($increase) > 0) {
                $item->increase = round(array_sum($increase) / count($increase), 2);
                $item->volume = array_sum($value);
            }

            $item->count = isset($count[$item->id]) ? $count[$item->id] : 0;

            return $item;
        })->filter(function ($item) {
            return $item->count > 0;
        })->sortByDesc(isset($select['order']) ? $select['order'] : 'increase')->values();

        return [
            'data' => $data,
            'total' => $data->count(),
        ];
    }
}
