<?php

namespace App\Repositories;

use App\Models\Tag;

class TagRepository extends Repository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Tag::query()->select('id', 'name')->get();
    }
}
