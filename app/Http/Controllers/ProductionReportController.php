<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatchDetailProcess;
use App\Models\ProductionPlanningItem;
use App\Models\RawMaterialDetailProcess;
use Illuminate\Http\Request;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $planningItems =
            ProductionPlanningItem::with([
                'productVariant.product',
                'productionPlanning'
            ])

            ->whereHas(
                'productionPlanning',
                function ($q) use ($request) {

                    if (
                        $request->filled('start_date')
                        &&
                        $request->filled('end_date')
                    ) {

                        $q->whereBetween(
                            'start_date',
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    }
                }
            )

            ->get();

        $report =
            $planningItems->map(function ($item) {

                /*
                |--------------------------------------------------------------------------
                | STAGE 1 OUTPUT
                |--------------------------------------------------------------------------
                */

                $stage1Output =
                    RawMaterialDetailProcess::where(
                        'stage',
                        'overdeck_tangan'
                    )

                    ->whereHas(
                        'detail',
                        function ($q) use ($item) {

                            $q->where(
                                'production_planning_item_id',
                                $item->id
                            );
                        }
                    )

                    ->sum('qty_confirmed');

                /*
                |--------------------------------------------------------------------------
                | STAGE 2 OUTPUT
                |--------------------------------------------------------------------------
                */

                $stage2Output =
                    ProductionBatchDetailProcess::where(
                        'stage',
                        'overdeck_bawah'
                    )

                    ->whereHas(
                        'detail',
                        function ($q) use ($item) {

                            $q->where(
                                'production_planning_item_id',
                                $item->id
                            );
                        }
                    )

                    ->sum('qty_confirmed');

                return [

                    'product' =>
                        $item
                            ->productVariant
                            ->product
                            ->name,

                    'size' =>
                        $item
                            ->productVariant
                            ->size,

                    'planning_target' =>
                        $item->estimated_qty,

                    'stage1_output' =>
                        $stage1Output,

                    'stage2_output' =>
                        $stage2Output,
                ];
            });

        return view(
            'report.productionReport',
            compact('report')
        );
    }
}
