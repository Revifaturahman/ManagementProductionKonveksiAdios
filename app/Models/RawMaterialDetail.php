<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialDetail extends Model
{
    protected $fillable = [
        'raw_material_id',
        'production_planning_item_id',
        'product_variant_id',
        'weight',
        'qty_result',
    ];

    // public function worker()
    // {
    //     return $this->belongsTo(Worker::class);
    // }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // public function courier()
    // {

    //     return $this->belongsTo(User::class, 'confirmed_by_courier_id');

    // }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function processes()
    {
        return $this->hasMany(RawMaterialDetailProcess::class);
    }

    public function productionPlanningItem()
    {
        return $this->belongsTo(
            ProductionPlanningItem::class
        );
    }
}