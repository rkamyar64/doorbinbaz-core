<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AriaCron extends Model
{
    protected $fillable = [
        'wc_id',
        'name',
        'slug',
        'permalink',
        'sku',
        'description',
        'price',
        'regular_price',
        'images',
        'is_in_stock',
        'maximum',
        'other',
        'insert_wordpress'
    ];

    protected $casts = [
        'images' => 'array',
        'other' => 'array'
    ];
}
