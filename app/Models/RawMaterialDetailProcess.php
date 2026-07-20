<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialDetailProcess extends Model
{
    // Kolom yang bisa diisi massal
    protected $fillable = [
        'raw_material_detail_id',
        'stage',
        'worker_id',
        'sequence',
        'qty_confirmed',
    ];

    /**
     * Relasi ke detail bahan mentah
     */
    public function detail()
    {
        return $this->belongsTo(RawMaterialDetail::class, 'raw_material_detail_id');
    }

    /**
     * Relasi ke pekerja
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Relasi ke kurir
     */
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
            'raw_material_detail_process_id'
        );
    }

    public function latestDelivery()
    {
        return $this->hasOne(
            ProcessDelivery::class,
            'raw_material_detail_process_id'
        )->latestOfMany();
    }
}
