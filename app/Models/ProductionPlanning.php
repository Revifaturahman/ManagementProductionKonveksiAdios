<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlanning extends Model
{
    protected $fillable = [
        'production_period_id',
        'raw_material_master_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function period()
    {
        return $this->belongsTo(
            ProductionPeriod::class,
            'production_period_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            ProductionPlanningItem::class
        );
    }

    public function rawMaterialMaster()
    {
        return $this->belongsTo(
            RawMaterialMaster::class
        );
    }
}
