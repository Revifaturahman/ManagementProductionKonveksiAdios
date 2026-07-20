<?php

namespace App\Models;

// use App\Models\Courier;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    protected $table = 'production_batches';
    protected $fillable = [
        'courier_id',
        'worker_id',
        'type',
        'date',
        'status',
        'cycle_started_at',
    ];

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function details()
    {
        return $this->hasMany(ProductionBatchDetail::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'type_product');
    }

    public function productionPlanningItem()
    {
        return $this->belongsTo(
            ProductionPlanningItem::class
        );
    }
}
