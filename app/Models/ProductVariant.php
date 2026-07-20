<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'minimum_stock',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    | Semi product stock
    */
    public function semiProduct()
    {
        return $this->hasOne(SemiProduct::class, 'product_variant_id');
    }

    public function finishedProduct()
    {
        return $this->hasOne(FinishedProduct::class);
    }    

    /*
    | Raw material items
    */
    public function rawMaterialItems()
    {
        return $this->hasMany(RawMaterialDetail::class);
    }
    
    // public function productionPlannings()
    // {
    //     return $this->hasMany(
    //         ProductionPlanning::class
    //     );
    // }

    public function planningItems()
    {
        return $this->hasMany(
            ProductionPlanningItem::class,
            'product_variant_id'
        );
    }
}