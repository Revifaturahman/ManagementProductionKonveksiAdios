<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialStockMovement extends Model
{
    protected $fillable = [
        'raw_material_master_id',
        'type',
        'qty_kg',
        'transaction_date',
        'notes'
    ];

    public function rawMaterialMaster()
    {
        return $this->belongsTo(
            RawMaterialMaster::class
        );
    }
}
