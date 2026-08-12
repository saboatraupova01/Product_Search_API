<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'brand',
        'price',
        'quantity',
        'rating',
        'is_active',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'price' => 'double',
        'quantity' => 'integer',
        'rating' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'date:Y-m-d',
    ];
}
