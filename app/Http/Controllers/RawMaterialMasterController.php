<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialMaster;
use App\Models\RawMaterialStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RawMaterialMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('productionPlanning.rawMaterialMaster', [

        'materials' =>
            RawMaterialMaster::with(
                'stock'
            )->latest()->get()

    ]);
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {

            // Cek nama sudah ada
            if (RawMaterialMaster::where('name', $request->name)->exists()) {

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Data bahan baku gagal ditambahkan karena nama bahan baku sudah terdaftar.'
                    );
            }

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | CREATE MASTER
            |--------------------------------------------------------------------------
            */

            $material = RawMaterialMaster::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE DEFAULT STOCK
            |--------------------------------------------------------------------------
            */

            RawMaterialStock::create([
                'raw_material_master_id' => $material->id,
                'stock_kg' => 0,
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Bahan berhasil ditambahkan.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan saat menambahkan bahan baku.'
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
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {

            $material = RawMaterialMaster::findOrFail($id);

            // Nama sudah digunakan data lain
            if (
                RawMaterialMaster::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists()
            ) {

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Data bahan baku gagal diubah karena nama bahan baku sudah terdaftar.'
                    );
            }

            $material->fill([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // Tidak ada perubahan
            if (!$material->isDirty()) {

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Data bahan baku gagal diubah karena tidak terdapat perubahan data.'
                    );
            }

            $material->save();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Bahan berhasil diupdate.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan saat memperbarui bahan baku.'
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | FIND MATERIAL
            |--------------------------------------------------------------------------
            */

            $material = RawMaterialMaster::with(
                'productionPlannings'
            )->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | CHECK RELATION
            |--------------------------------------------------------------------------
            */

            if (
                $material->productionPlannings->count() > 0
            ) {

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Bahan sudah digunakan di production planning'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE
            |--------------------------------------------------------------------------
            */

            $material->delete();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Bahan berhasil dihapus'
                );

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}
