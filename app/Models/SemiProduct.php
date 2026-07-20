<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemiProduct extends Model
{
    protected $fillable = [
        'product_variant_id',
        'qty',
        'stock_opname_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}