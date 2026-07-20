<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionBatchDetailProcess extends Model
{
    protected $table = 'production_batch_detail_processes';
    protected $fillable = [
        'production_batch_detail_id',
        'stage',
        'worker_id',
        'sequence',
        'qty_confirmed',
    ];

    public function detail()
    {
        return $this->belongsTo(ProductionBatchDetail::class, 'production_batch_detail_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function deliveries()
    {
        return $this->hasMany(ProcessDelivery::class);
    }

    public function progresses()
    {
        return $this->hasMany(
            ProcessProgress::class,
            'production_batch_detail_process_id'
        );
    }
}
