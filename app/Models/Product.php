<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'ratio_per_kg',
        'allocation_ratio',
        'category_id',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function rawMaterialDetails()
    {
        return $this->hasMany(RawMaterialDetail::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
}
