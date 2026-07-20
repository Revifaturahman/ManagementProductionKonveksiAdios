<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlanningItem extends Model
{
    protected $fillable = [
        'production_planning_id',
        'product_variant_id',
        'priority_order',
        'estimated_kg',
        'remaining_kg',
        'estimated_qty'
    ];
    public function productionPlanning()
    {
        return $this->belongsTo(
            ProductionPlanning::class
        );
    }

    public function productVariant()
    {
        return $this->belongsTo(
            ProductVariant::class
        );
    }

    public function rawMaterialDetails()
    {
        return $this->hasMany(
            RawMaterialDetail::class
        );
    }

    public function productionBatchesDeatails()
    {
        return $this->hasMany(
            ProductionBatch::class
        );
    }
}
