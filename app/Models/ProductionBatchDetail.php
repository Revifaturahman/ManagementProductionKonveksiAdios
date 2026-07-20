<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionBatchDetail extends Model
{
    protected $table = 'production_batch_details';
     protected $fillable = [
        'production_batch_id',
        'production_planning_item_id',
        'product_variant_id',
        'qty'
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function processes()
    {
        return $this->hasMany(ProductionBatchDetailProcess::class, 'production_batch_detail_id');
    }
}
