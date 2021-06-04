<?php

namespace App\Models;

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
