<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialStock extends Model
{
    protected $fillable = [
        'raw_material_master_id',
        'stock_kg',
    ];

    public function rawMaterialMaster()
    {
        return $this->belongsTo(
            RawMaterialMaster::class
        );
    }
}
