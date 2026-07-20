<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'courier_id',
        'date',
        'status',
        'cycle_started_at',
    ];

    public function details()
    {
        return $this->hasMany(RawMaterialDetail::class);
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