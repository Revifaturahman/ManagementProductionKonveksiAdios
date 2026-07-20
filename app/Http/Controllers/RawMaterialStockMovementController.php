<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialMaster;
use App\Models\RawMaterialStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\RawMaterialStock;

class RawMaterialStockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view(
            'productionPlanning.rawMaterialStockMovement',
            [

                'raw_material_stock_movements' =>

                    RawMaterialStockMovement::with(
                        'rawMaterialMaster'
                    )->latest()->get(),

                'materials' =>

                    RawMaterialMaster::latest()->get()

            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'raw_material_master_id' => 'required|exists:raw_material_masters,id',

            'qty_kg' => 'required|numeric|min:0.01',

            'transaction_date' => 'required|date',

            'notes' => 'nullable|string',
        ], [
            'qty_kg.min' => 'Berat bahan harus lebih dari 0 KG.',
        ]);

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | CREATE MOVEMENT
            |--------------------------------------------------------------------------
            */

            $movement =
                RawMaterialStockMovement::create([

                    'raw_material_master_id' =>
                        $request->raw_material_master_id,

                    'type' => 'in',

                    'qty_kg' =>
                        $request->qty_kg,

                    'transaction_date' =>
                        $request->transaction_date,

                    'notes' =>
                        $request->notes,

                ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE STOCK
            |--------------------------------------------------------------------------
            */

            $stock =
                RawMaterialStock::where(
                    'raw_material_master_id',
                    $request->raw_material_master_id
                )->first();

            /*
            |--------------------------------------------------------------------------
            | ADD STOCK
            |--------------------------------------------------------------------------
            */

            if ($stock) {

                $stock->increment(
                    'stock_kg',
                    $request->qty_kg
                );

            } else {

                RawMaterialStock::create([

                    'raw_material_master_id' =>
                        $request->raw_material_master_id,

                    'stock_kg' =>
                        $request->qty_kg,

                ]);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Penerimaan bahan berhasil ditambahkan'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,string $id)
    {
        $request->validate([

            'raw_material_master_id' =>
                'required|exists:raw_material_masters,id',

            'qty_kg' =>
                'required|numeric|min:0.01',

            'transaction_date' =>
                'required|date',

            'notes' =>
                'nullable|string',

        ]);

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | FIND MOVEMENT
            |--------------------------------------------------------------------------
            */

            $movement =
                RawMaterialStockMovement::findOrFail($id);

            $movement->fill([
                'raw_material_master_id' => $request->raw_material_master_id,
                'qty_kg' => $request->qty_kg,
                'transaction_date' => $request->transaction_date,
                'notes' => $request->notes,
            ]);

            if (!$movement->isDirty()) {

                DB::rollBack();

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Penerimaan bahan gagal diubah karena tidak terdapat perubahan data.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | ROLLBACK OLD STOCK
            |--------------------------------------------------------------------------
            */

           $oldStock = RawMaterialStock::where(
                'raw_material_master_id',
                $movement->getOriginal('raw_material_master_id')
            )->first();

            if ($oldStock) {

                $oldStock->decrement(
                    'stock_kg',
                    $movement->getOriginal('qty_kg')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE MOVEMENT
            |--------------------------------------------------------------------------
            */

            $movement->save();

            /*
            |--------------------------------------------------------------------------
            | APPLY NEW STOCK
            |--------------------------------------------------------------------------
            */

            $newStock =
                RawMaterialStock::where(
                    'raw_material_master_id',
                    $request->raw_material_master_id
                )->first();

            if ($newStock) {

                $newStock->increment(
                    'stock_kg',
                    $request->qty_kg
                );

            } else {

                RawMaterialStock::create([

                    'raw_material_master_id' =>
                        $request->raw_material_master_id,

                    'stock_kg' =>
                        $request->qty_kg,

                ]);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Penerimaan bahan berhasil diupdate'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | FIND MOVEMENT
            |--------------------------------------------------------------------------
            */

            $movement =
                RawMaterialStockMovement::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | FIND STOCK
            |--------------------------------------------------------------------------
            */

            $stock =
                RawMaterialStock::where(
                    'raw_material_master_id',
                    $movement->raw_material_master_id
                )->first();

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK STOCK
            |--------------------------------------------------------------------------
            */

            if ($stock) {

                /*
                |--------------------------------------------------------------------------
                | PREVENT NEGATIVE STOCK
                |--------------------------------------------------------------------------
                */

                if (
                    $stock->stock_kg < $movement->qty_kg
                ) {

                    return redirect()
                        ->back()
                        ->with(
                            'error',
                            'Stock tidak mencukupi untuk rollback delete'
                        );
                }

                $stock->decrement(
                    'stock_kg',
                    $movement->qty_kg
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE MOVEMENT
            |--------------------------------------------------------------------------
            */

            $movement->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Penerimaan bahan berhasil dihapus'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}
