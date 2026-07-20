<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPeriod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'status'
    ];

    public function plannings()
    {
        return $this->hasMany(
            ProductionPlanning::class
        );
    }
}
