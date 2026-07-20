<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialMaster extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function stock()
    {
        return $this->hasOne(
            RawMaterialStock::class
        );
    }

    public function stockMovements()
    {
        return $this->hasMany(
            RawMaterialStockMovement::class
        );
    }

    public function productionPlannings()
    {
        return $this->hasMany(
            ProductionPlanning::class
        );
    }
}
