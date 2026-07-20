<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'role',
        'overdeck_type',
        'address',
        'rate_per_piece',
        'latitude',
        'longitude',
    ];

    public function rawMaterialDetail()
    {
        return $this->hasMany(RawMaterialDetail::class);
    }

    public function productionBatchDetail()
    {
        return $this->hasMany(ProductionBatchDetail::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}