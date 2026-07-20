<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessProgress extends Model
{
    protected $table = 'process_progresses';
    protected $fillable = [
        'raw_material_id',
        'production_batch_id',
        'raw_material_detail_process_id',
        'production_batch_detail_process_id',
        'progress_date',
        'qty_progress',
        'notes'
    ];

    public function rawMaterialProcess()
    {
        return $this->belongsTo(
            RawMaterialDetailProcess::class,
            'raw_material_detail_process_id'
        );
    }

    public function productionBatchProcess()
    {
        return $this->belongsTo(
            ProductionBatchDetailProcess::class,
            'production_batch_detail_process_id'
        );
    }

    public function rawMaterial()
    {
        return $this->belongsTo(
            RawMaterial::class,
            'raw_material_id'
        );
    }

    public function productionBatch()
    {
        return $this->belongsTo(
            ProductionBatch::class,
            'production_batch_id'
        );
    }
}
