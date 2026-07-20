<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessDelivery extends Model
{
    protected $fillable = [
        // 'raw_material_id',
        // 'production_batch_id',
        'raw_material_detail_process_id',
        'production_batch_detail_process_id',
        'worker_id',
        'courier_id',
        'delivered_qty',
        'delivered_unit',
        'received_qty',
        'received_unit',
        'type',
        'destination_type',
        'status',
        'started_at',
        'arrived_at',
        'finished_at'
    ];

    public function rawMaterialDetailProcess()
    {
        return $this->belongsTo(
            RawMaterialDetailProcess::class,
            'raw_material_detail_process_id'
        );
    }

    public function productionBatchDetailProcess()
    {
        return $this->belongsTo(
            ProductionBatchDetailProcess::class,
            'production_batch_detail_process_id'
        );
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
