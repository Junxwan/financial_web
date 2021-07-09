<?php

namespace App\Services;

use App\Repositories\PriceRepository;
use App\Repositories\TagRepository;
use Illuminate\Support\Arr;

class Industry
{
    /**
     * @var PriceRepository
     */
    private PriceRepository $price;

    /**
     * @var TagRepository
     */
    private TagRepository $tag;

    /**
     * Industry constructor.
     *
     * @param PriceRepository $price
     * @param TagRepository $tag
     */
    public function __construct(PriceRepository $price, TagRepository $tag)
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
        $tag = $this->tag->allByShowPrice();

        if (isset($select['search'])) {
            $search = $select['search'];
            if (isset($search['start_date']) && ! is_null($search['start_date'])) {
                $date = $search['start_date'];
            }
        }

        $price = $this->price->getByTag($tag->pluck('id')->toArray(), $date);

        $data = $tag->map(function ($item) use ($price) {
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

            return $item;
        })->sortByDesc(isset($select['order']) ? $select['order'] : 'increase')->values();

        return [
            'data' => $data,
            'total' => $data->count(),
        ];
    }
}
