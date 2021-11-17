<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'code',
        'name',
        'classification_id',
    ];

    public $timestamps = false;
}
