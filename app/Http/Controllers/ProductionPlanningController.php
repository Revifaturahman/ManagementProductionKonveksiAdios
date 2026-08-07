<?php

namespace App\Http\Controllers;

use App\Models\ProductionPeriod;
use App\Models\ProductionPlanning;
use App\Models\ProductionPlanningItem;
use App\Models\ProductVariant;
use App\Models\RawMaterialMaster;
use App\Models\RawMaterialStock;
use App\Services\ProductionPlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionPlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductionPlanning::with([
            'period',
            'rawMaterialMaster',
            'items.productVariant.product'
        ]);

        if (
            $request->filled(
                'production_period_id'
            )
        ) {

            $query->where(
                'production_period_id',
                $request->production_period_id
            );
        }

        $production_plannings =
            $query
                ->latest()
                ->get();

        $periods = ProductionPeriod::where('status', 'pending')
            ->orderByDesc('start_date')
            ->get();

        return view(
            'productionPlanning.productionPlanning',
            [

                'production_plannings' =>
                    $production_plannings,

                'periods' =>
                    $periods,

                'product_variants' =>
                    ProductVariant::with(
                        'product'
                    )->get(),

                'raw_material_masters' =>
                    RawMaterialMaster::with(
                        'stock'
                    )->get(),
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
        // dd($request->all());
        $request->validate([

            'production_period_id' =>
                'required|exists:production_periods,id',

            'raw_material_master_id' =>
                'required|exists:raw_material_masters,id',

            'product_variant_ids' => 'required|array',

            'estimated_kgs' => 'required|array',

            'priority_orders' => 'required|array',

        ]);

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | CHECK PERIOD STATUS
            |--------------------------------------------------------------------------
            */

            $period = ProductionPeriod::findOrFail(
                $request->production_period_id
            );

            if ($period->status == 'active') {

                DB::rollBack();

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Perencanaan produksi gagal dibuat karena periode produksi sedang aktif.'
                    );
            }

            if ($period->status == 'finished') {

                DB::rollBack();

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Perencanaan produksi gagal dibuat karena periode produksi telah selesai.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL KG
            |--------------------------------------------------------------------------
            */

            $totalKg = collect($request->estimated_kgs)
                ->sum();

            /*
            |--------------------------------------------------------------------------
            | CHECK STOCK
            |--------------------------------------------------------------------------
            */

            $rawMaterial =
                RawMaterialMaster::with('stock')
                    ->findOrFail(
                        $request->raw_material_master_id
                    );

            $usableKg =
                max(
                    0,
                    $rawMaterial->stock->stock_kg - 40
                );

            // dd([
            //     'stock' =>
            //         $rawMaterial->stock->stock_kg,

            //     'usableKg' =>
            //         $usableKg,

            //     'totalKg' =>
            //         $totalKg,
            // ]);

            if ($usableKg < $totalKg) {

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Stock bahan baku tidak mencukupi'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE HEADER
            |--------------------------------------------------------------------------
            */
            // dd('SEBELUM CREATE HEADER');

            $planning = ProductionPlanning::create([

                'production_period_id' =>
                    $request->production_period_id,

                'raw_material_master_id' =>
                    $request->raw_material_master_id,

                'notes' =>
                    $request->notes,

                'status' =>
                    'pending',
            ]);

            ProductionPeriod::where(
                'id',
                $request->production_period_id
            )->update([
                'status' => 'active'
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($request->product_variant_ids as $i => $variantId) {

                $variant = ProductVariant::with('product')
                    ->findOrFail($variantId);

                /*
                |--------------------------------------------------------------------------
                | GENERATE ESTIMATED QTY
                |--------------------------------------------------------------------------
                |
                | ratio_per_kg dari product
                |
                */

                $estimatedKg =
                    $request->estimated_kgs[$i];

                $estimatedQty =
                    $estimatedKg *
                    $variant->product->ratio_per_kg;

                ProductionPlanningItem::create([

                    'production_planning_id' =>
                        $planning->id,

                    'product_variant_id' =>
                        $variantId,

                    'priority_order' =>
                        $request->priority_orders[$i],

                    'estimated_kg' =>
                        $estimatedKg,

                    'remaining_kg' =>
                        $estimatedKg,

                    'estimated_qty' =>
                        $estimatedQty,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | REDUCE RAW MATERIAL STOCK
            |--------------------------------------------------------------------------
            */

            $rawMaterial->stock->decrement(
                'stock_kg',
                $totalKg
            );

            DB::commit();

            return redirect()
                ->back()
                ->with('success',
                    'Perencanaan produksi berhasil dibuat'
                );

        } catch (\Exception $e) {

            dd(
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
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

            'production_period_id' =>
                'required|exists:production_periods,id',

            'raw_material_master_id' =>
                'required|exists:raw_material_masters,id',

            'product_variant_ids' =>
                'required|array',

            'estimated_kgs' =>
                'required|array',

            'priority_orders' =>
                'required|array',
        ]);

        DB::beginTransaction();

        try {

            $planning = ProductionPlanning::with([
                'items',
                'rawMaterialMaster.stock'
            ])->findOrFail($id);

            if ($planning->status == 'process') {

                DB::rollBack();

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Perencanaan produksi gagal diubah karena sudah diproses pada produksi tahap 1.'
                    );
            }

            if ($planning->status == 'finished') {

                DB::rollBack();

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Perencanaan produksi gagal diubah karena telah selesai diproses.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | RETURN OLD STOCK
            |--------------------------------------------------------------------------
            */

            $oldTotalKg =
                $planning->items->sum('estimated_kg');

            $oldRawMaterial =
                $planning->rawMaterialMaster;

            if (
                $oldRawMaterial &&
                $oldRawMaterial->stock
            ) {

                $oldRawMaterial->stock->increment(
                    'stock_kg',
                    $oldTotalKg
                );
            }

            /*
            |--------------------------------------------------------------------------
            | NEW TOTAL KG
            |--------------------------------------------------------------------------
            */

            $newTotalKg =
                collect($request->estimated_kgs)
                    ->sum();

            $newRawMaterial =
                RawMaterialMaster::with('stock')
                    ->findOrFail(
                        $request->raw_material_master_id
                    );

            /*
            |--------------------------------------------------------------------------
            | CHECK STOCK
            |--------------------------------------------------------------------------
            */

            if (
                !$newRawMaterial->stock ||
                $newRawMaterial->stock->stock_kg < $newTotalKg
            ) {

                DB::rollBack();

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Stock bahan baku tidak cukup'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | REDUCE NEW STOCK
            |--------------------------------------------------------------------------
            */

            $newRawMaterial->stock->decrement(
                'stock_kg',
                $newTotalKg
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE HEADER
            |--------------------------------------------------------------------------
            */

            $planning->update([

                'production_period_id' =>
                    $request->production_period_id,

                'raw_material_master_id' =>
                    $request->raw_material_master_id,

                'notes' =>
                    $request->notes,
            ]);

            /*
            |--------------------------------------------------------------------------
            | DELETE OLD ITEMS
            |--------------------------------------------------------------------------
            */

            $planning->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | CREATE NEW ITEMS
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->product_variant_ids
                as $i => $variantId
            ) {

                $variant =
                    ProductVariant::with('product')
                        ->findOrFail($variantId);

                $estimatedKg =
                    $request->estimated_kgs[$i];

                $estimatedQty =
                    $estimatedKg *
                    $variant->product->ratio_per_kg;

                ProductionPlanningItem::create([

                    'production_planning_id' =>
                        $planning->id,

                    'product_variant_id' =>
                        $variantId,

                    'priority_order' =>
                        $request->priority_orders[$i],

                    'estimated_kg' =>
                        $estimatedKg,

                    'remaining_kg' =>
                        $estimatedKg,

                    'estimated_qty' =>
                        $estimatedQty,
                ]);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Perencanaan produksi berhasil diupdate'
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
        DB::beginTransaction();

        try {

            $planning = ProductionPlanning::with([
                'items',
                'rawMaterialMaster.stock'
            ])->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | RETURN STOCK
            |--------------------------------------------------------------------------
            */

            $totalKg =
                $planning->items->sum(
                    'estimated_kg'
                );

            $rawMaterial =
                $planning->rawMaterialMaster;

            if (
                $rawMaterial &&
                $rawMaterial->stock
            ) {

                $rawMaterial->stock->increment(
                    'stock_kg',
                    $totalKg
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE PLANNING
            |--------------------------------------------------------------------------
            */

            $planning->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Perencanaan produksi berhasil dihapus'
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

    public function generateSuggestions(
        Request $request,
        ProductionPlanningService $service
    )
    {
        $request->validate([
            'raw_material_master_id'
                => 'required|exists:raw_material_masters,id'
        ]);

        $rawMaterial =
            RawMaterialMaster::with('stock')
                ->findOrFail(
                    $request->raw_material_master_id
                );

        $suggestions =
            $service->generateSuggestionsFromRawMaterial(
                $rawMaterial
            );

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}
