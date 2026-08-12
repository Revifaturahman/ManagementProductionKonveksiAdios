<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use App\Models\ProcessDelivery;
use App\Models\ProductionBatch;
use App\Models\ProductionPlanning;
use App\Models\ProductVariant;
use App\Models\RawMaterial;
use App\Models\RawMaterialStock;
use App\Models\SemiProduct;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;

class FrontPageController extends Controller
{
    public function index()
    {
        // ==========================
        // KPI
        // ==========================

        $rawMaterialKg = RawMaterialStock::sum('stock_kg');

        $semiProductPcs = SemiProduct::sum('qty');

        $finishedProductPcs = FinishedProduct::sum('qty');

        $lowStocks = ProductVariant::query()
            ->join('finished_products', 'finished_products.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereColumn(
                'finished_products.qty',
                '<',
                'product_variants.minimum_stock'
            )
            ->select(
                'products.name as product_name',
                'product_variants.size',
                'finished_products.qty',
                'product_variants.minimum_stock'
            )
            ->get();

        // ==========================
        // OPERASIONAL
        // ==========================

        $nearestPlannings = ProductionPlanning::with('items')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($planning) {

                $target = $planning->items->sum('estimated_kg');

                $remaining = $planning->items->sum('remaining_kg');

                $allocated = $target - $remaining;

                $progress = $target > 0
                    ? round(($allocated / $target) * 100)
                    : 0;

                $planning->target_kg = $target;
                $planning->allocated_kg = $allocated;
                $planning->remaining_kg = $remaining;
                $planning->progress_percent = $progress;

                return $planning;
            });

        $currentPlanning = ProductionPlanning::with('items')
            ->whereIn('status', ['pending', 'process'])
            ->oldest('created_at')
            ->first();

        $planningTargetKg = 0;
        $planningAllocatedKg = 0;
        $planningRemainingKg = 0;
        $planningProgress = 0;

        if ($currentPlanning) {

            $planningTargetKg =
                $currentPlanning->items->sum('estimated_kg');

            $planningRemainingKg =
                $currentPlanning->items->sum('remaining_kg');

            $planningAllocatedKg =
                $planningTargetKg - $planningRemainingKg;

            $planningProgress =
                $planningTargetKg > 0
                    ? round(
                        ($planningAllocatedKg / $planningTargetKg) * 100
                    )
                    : 0;
        }

        $activeProduction =
            RawMaterial::where('status', 'process')->count()
            +
            ProductionBatch::where('status', 'process')->count();

        $activeWorkers = User::where('role', 'courier')->where('is_active', 1)->count();
        $activeWorkersProduction = User::where('role', 'production')->where('is_active', 1)->count();
        // ==========================
        // LOW STOCK
        // ==========================

        $lowStockProducts = ProductVariant::query()
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join(
                'finished_products',
                'finished_products.product_variant_id',
                '=',
                'product_variants.id'
            )
            ->whereColumn(
                'finished_products.qty',
                '<',
                'product_variants.minimum_stock'
            )
            ->select([
                'products.name',
                'product_variants.size',
                'finished_products.qty',
                'product_variants.minimum_stock'
            ])
            ->orderBy('finished_products.qty')
            ->limit(10)
            ->get();

        return view('frontPage', compact(
            'rawMaterialKg',
            'semiProductPcs',
            'finishedProductPcs',
            'lowStocks',
            'activeProduction',
            'activeWorkers',
            'lowStockProducts',

            'currentPlanning',
            'planningTargetKg',
            'planningAllocatedKg',
            'planningRemainingKg',
            'planningProgress',

            'nearestPlannings',
            'activeWorkersProduction'
        ));
    }
}
