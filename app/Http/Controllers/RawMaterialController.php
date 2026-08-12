<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Models\ProcessDelivery;
use App\Models\ProcessProgress;
use App\Models\Product;
use App\Models\ProductionPeriod;
use App\Models\ProductionPlanning;
use App\Models\ProductionPlanningItem;
use App\Models\ProductVariant;
use App\Models\RawMaterial;
use App\Models\RawMaterialDetail;
use App\Models\RawMaterialDetailProcess;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RawMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         $query = RawMaterial::with([

            'courier',

            'details',

            'details.variant',

            'details.processes',

            'details.processes.worker',

            'details.productionPlanningItem',

            'details.productionPlanningItem.productVariant',

            'details.productionPlanningItem.productVariant.product',

        ]);

        if (
            $request->filled('start_date')
            &&
            $request->filled('end_date')
        ) {

            $query->whereBetween(
                'date',
                [
                    $request->start_date,
                    $request->end_date
                ]
            );
        }

        $rawMaterials = $query->get();
        return view('production.rawMaterial', [

            'rawMaterials' => $rawMaterials,

            /*
            |--------------------------------------------------------------------------
            | COURIERS
            |--------------------------------------------------------------------------
            */

            'couriers' => User::where('role', 'courier')
                ->where('is_active', 1)
                ->whereDoesntHave('deliveries', function ($q) {
                    $q->whereIn('status', [
                        'pending',
                        'started',
                        'arrive'
                    ]);
                })
                ->withCount([
                    'deliveries as today_deliveries_count' => function ($q) {
                        $q->whereDate('created_at', today());
                    }
                ])
                ->orderBy('today_deliveries_count', 'asc')
                ->inRandomOrder()
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | CUTTERS
            |--------------------------------------------------------------------------
            */

            'cutters' => Worker::where('role', 'cutter')
                ->get()
                ->map(function ($worker) {
                    $worker->remaining_qty =
                        $this->calculateRemainingQty(
                            $worker->id,
                            'cutter'
                        );

                    return $worker;
                })
                ->sortBy('remaining_qty')
                ->values(),
            /*
            |--------------------------------------------------------------------------
            | OVERDECK TANGAN
            |--------------------------------------------------------------------------
            */

            'overdeck_hands' => Worker::where('role', 'overdeck')
                ->where('overdeck_type', 'tangan')
                ->get()
                ->map(function ($worker) {
                    $worker->remaining_qty =
                        $this->calculateRemainingQty(
                            $worker->id,
                            'overdeck_tangan'
                        );

                    return $worker;
                })
                ->sortBy('remaining_qty')
                ->values(),

            /*
            |--------------------------------------------------------------------------
            | PLANNING ITEMS
            |--------------------------------------------------------------------------
            */

            'planningItems' => ProductionPlanningItem::with([
                'productVariant.product'
            ])
            ->where(
                'remaining_kg',
                '>',
                0
            )
            ->orderBy(
                'priority_order'
            )
            ->get(),

            'generatedTaskItems'
                => $this->generateTaskItems(),

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
        // dd($request->all());
        $validated = $request->validate([
            'courier_id' => 'required',

            'cutter_worker_id' => 'required',

            'overdeck_worker_id' => 'required',

            'date' => 'required',

            'planning_item_ids' => 'required|array',

            'planning_item_ids.*' =>
                'required|exists:production_planning_items,id',

            'weights' => 'required|array',

            'weights.*' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated) {
            /*
            |--------------------------------------------------------------------------
            | UPDATE ROUTING AKTIF AYU -> OVERDECK TERBARU
            |--------------------------------------------------------------------------
            |
            | Semua pekerjaan cutter Ayu yang BELUM pernah masuk
            | ke overdeck akan mengikuti worker overdeck terbaru.
            |
            */
                RawMaterialDetailProcess::where(
                    'stage',
                    'overdeck_tangan'
                )
                ->whereHas(
                    'detail.processes',
                    function ($q) use ($validated) {

                        $q->where(
                            'stage',
                            'cutter'
                        )
                        ->where(
                            'worker_id',
                            $validated['cutter_worker_id']
                        );
                    }
                )

                ->whereDoesntHave('deliveries')

                ->update([

                    'worker_id' =>
                        $validated['overdeck_worker_id']
                ]);

            /*
            |--------------------------------------------------------------------------
            | HEADER
            |--------------------------------------------------------------------------
            */
            $rawMaterial = RawMaterial::create([
                'courier_id' => $validated['courier_id'],
                'date'       => $validated['date'],
                'status'     => 'pending',
            ]);

            ProductionPlanning::whereHas('items', function ($query) use ($validated) {

                $query->whereIn(
                    'id',
                    $validated['planning_item_ids']
                );

            })->update([
                'status' => 'process'
            ]);

            /*
            |--------------------------------------------------------------------------
            | DETAIL
            |--------------------------------------------------------------------------
            */
            foreach ($validated['planning_item_ids'] as $i => $planningItemId) {
                $planningItem =
                    ProductionPlanningItem::findOrFail(
                        $planningItemId
                    );
                $weight =
                    $validated['weights'][$i];

                if ($weight > $planningItem->remaining_kg) {

                    throw ValidationException::withMessages([
                        'weights' => 'Berat melebihi sisa planning.'
                    ]);
                }
                $detail = RawMaterialDetail::create([

                    'raw_material_id' =>
                        $rawMaterial->id,

                    'production_planning_item_id' =>
                        $planningItem->id,

                    'product_variant_id' =>
                        $planningItem->product_variant_id,

                    'weight' =>
                        $weight,
                ]);
                $planningItem->decrement(
                    'remaining_kg',
                    $weight
                );
                $planningItem->refresh();
                // if ($planningItem->remaining_kg <= 0) {

                //     $planningItem->update([
                //         'status' => 'finished'
                //     ]);
                // }

                /*
                |--------------------------------------------------------------------------
                | PROCESS : CUTTER
                |--------------------------------------------------------------------------
                */
                $cutterProcess = RawMaterialDetailProcess::create([
                    'raw_material_detail_id' => $detail->id,

                    // planned worker
                    'worker_id' => $validated['cutter_worker_id'],

                    'stage' => 'cutter',

                    'sequence' => 1,
                ]);

                /*
                |--------------------------------------------------------------------------
                | PROCESS : OVERDECK
                |--------------------------------------------------------------------------
                |
                | Ini assignment default admin.
                | BUKAN berarti barang sudah ke sana.
                |
                */
                RawMaterialDetailProcess::create([
                    'raw_material_detail_id' => $detail->id,

                    // planned worker
                    'worker_id' => $validated['overdeck_worker_id'],

                    'stage' => 'overdeck_tangan',

                    'sequence' => 2,
                ]);

                /*
                |--------------------------------------------------------------------------
                | FIRST DELIVERY
                |--------------------------------------------------------------------------
                |
                | Barang baru benar-benar dibawa ke cutter.
                |
                */

                ProcessDelivery::create([

                    // 'raw_material_id' =>
                    //     $rawMaterial->id,

                    'raw_material_detail_process_id' =>
                        $cutterProcess->id,

                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY TYPE
                    |--------------------------------------------------------------------------
                    */

                    'type' => 'process',

                    'destination_type' => 'worker',

                    /*
                    |--------------------------------------------------------------------------
                    | DESTINATION WORKER
                    |--------------------------------------------------------------------------
                    */

                    'worker_id' =>
                        $validated['cutter_worker_id'],

                    /*
                    |--------------------------------------------------------------------------
                    | COURIER
                    |--------------------------------------------------------------------------
                    */

                    'courier_id' =>
                        $validated['courier_id'],

                    /*
                    |--------------------------------------------------------------------------
                    | ITEM
                    |--------------------------------------------------------------------------
                    */

                    'delivered_qty' =>
                        $validated['weights'][$i],

                    'delivered_unit' => 'kg',

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    'status' => 'pending',

                    'started_at' => null,

                    'arrived_at' => null,

                    'finished_at' => null,
                ]);
            }
            $planningIds = ProductionPlanningItem::whereIn(
                'id',
                $validated['planning_item_ids']
            )
            ->pluck('production_planning_id')
            ->unique();

            foreach ($planningIds as $planningId) {

                $planning = ProductionPlanning::find($planningId);

                $hasRemaining = $planning->items()
                    ->where('remaining_kg', '>', 0)
                    ->exists();

                if (!$hasRemaining) {
                    $planning->update([
                        'status' => 'finished'  
                    ]);
                }
            }

            try {

            $periodIds = ProductionPlanning::whereIn('id', $planningIds)
                ->pluck('production_period_id')
                ->unique();

            foreach ($periodIds as $periodId) {

                $hasUnfinishedPlanning = ProductionPlanning::where(
                    'production_period_id',
                    $periodId
                )
                ->where('status', '!=', 'finished')
                ->exists();

                if (!$hasUnfinishedPlanning) {

                    ProductionPeriod::where('id', $periodId)
                        ->update([
                            'status' => 'finished'
                        ]);
                }
            }

        } catch (\Throwable $e) {

            dd([
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }

        });

        return back()->with(
            'success',
            'Produksi Tahap 1 berhasil ditambahkan'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rawMaterial = RawMaterial::with([

            'details.variant.product',

            'details.processes.worker',

            'details.processes.deliveries',

        ])->findOrFail($id);

        /*
        =========================================
        FORMAT TRACKING
        =========================================
        */
        $tracking = $rawMaterial->details->map(function ($detail) {

            /*
            =========================================
            AMBIL SEMUA PROCESS
            =========================================
            */
            $processes = $detail->processes
                ->sortBy('sequence')
                ->values();

            /*
            =========================================
            DEFAULT PROCESS PERTAMA
            =========================================
            */
            $currentProcess = $processes->first();

            /*
            =========================================
            CEK DELIVERY TERAKHIR
            =========================================
            */
            foreach ($processes as $process) {

                $latestDelivery = $process->deliveries
                    ->sortByDesc('id')
                    ->first();

                /*
                =========================================
                JIKA PROCESS SUDAH FINISHED
                MAKA PINDAH KE NEXT PROCESS
                =========================================
                */
                if (
                    $latestDelivery &&
                    $latestDelivery->status == 'finished'
                ) {

                    $nextProcess = $processes
                        ->firstWhere(
                            'sequence',
                            $process->sequence + 1
                        );

                    if ($nextProcess) {
                        $currentProcess = $nextProcess;
                    }

                } else {

                    /*
                    =========================================
                    PROCESS INI MASIH BERJALAN
                    =========================================
                    */
                    $currentProcess = $process;

                    break;
                }
            }

            /*
            =========================================
            NEXT PROCESS
            =========================================
            */
            $nextProcess = $processes
                ->firstWhere(
                    'sequence',
                    $currentProcess->sequence + 1
                );

            return [

                'product' => $detail->variant->product->name,

                'size' => $detail->variant->size,

                'weight' => $detail->weight,

                'current_process' => [

                    'stage' => $currentProcess?->stage,

                    'worker' => $currentProcess?->worker?->name,

                    'sequence' => $currentProcess?->sequence,

                ],

                'next_process' => [

                    'stage' => $nextProcess?->stage,

                    'worker' => $nextProcess?->worker?->name,

                    'sequence' => $nextProcess?->sequence,

                ],

                'timeline' => $processes->map(function ($process) {

                    $latestDelivery = $process->deliveries
                        ->sortByDesc('id')
                        ->first();

                    $status = 'pending';

                    if ($latestDelivery) {

                        $status =
                            $latestDelivery->status;
                    }

                    return [

                        'stage' => $process->stage,

                        'worker' => $process->worker?->name,

                        'sequence' => $process->sequence,

                        'status' => $status,

                    ];
                }),

            ];
        });

        return response()->json([
            'id' => $rawMaterial->id,

            'date' => $rawMaterial->date,

            'status' => $rawMaterial->status,

            'tracking' => $tracking,
        ]);
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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([

            'courier_id' => 'required|exists:users,id',

            'cutter_worker_id' => 'required|exists:workers,id',

            'overdeck_worker_id' => 'required|exists:workers,id',

            'date' => 'required',

            'planning_item_ids' => 'required|array',

            'planning_item_ids.*' =>
                'required|exists:production_planning_items,id',

            'weights' => 'required|array',

            'weights.*' => 'required|numeric|min:0.01',
        ]);

        $rawMaterial = RawMaterial::findOrFail($id);

            if ($rawMaterial->status == 'process') {

                return back()->with(
                    'error',
                    'Produksi tahap 1 gagal diubah karena sedang dalam proses pengiriman.'
                );
            }

            if ($rawMaterial->status == 'finished') {

                return back()->with(
                    'error',
                    'Produksi tahap 1 gagal diubah karena telah selesai diproses.'
                );
            }

        DB::transaction(function () use ($validated, $rawMaterial, $id) {

            /*
            |--------------------------------------------------------------------------
            | Produksi Tahap 1
            |--------------------------------------------------------------------------
            */

            $rawMaterial->update([
                'courier_id' => $validated['courier_id'],

                'date' => $validated['date'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | DELETE OLD DETAILS
            |--------------------------------------------------------------------------
            |
            | Cascade:
            | detail -> process -> deliveries
            |
            */

            RawMaterialDetail::where(
                'raw_material_id',
                $id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | INSERT NEW DETAILS
            |--------------------------------------------------------------------------
            */

            foreach (
                $validated['planning_item_ids']
                as $i => $planningItemId
            ) {
                $planningItem =
                    ProductionPlanningItem::with(
                        'productVariant'
                    )
                    ->findOrFail(
                        $planningItemId
                    );

                /*
                |--------------------------------------------------------------------------
                | DETAIL
                |--------------------------------------------------------------------------
                */

                $detail = RawMaterialDetail::create([

                    'raw_material_id' => $id,

                    'production_planning_item_id'
                        => $planningItem->id,

                    'product_variant_id'
                        => $planningItem->product_variant_id,

                    'weight'
                        => $validated['weights'][$i],
                ]);

                /*
                |--------------------------------------------------------------------------
                | PROCESS : CUTTER
                |--------------------------------------------------------------------------
                */

                $cutterProcess = RawMaterialDetailProcess::create([

                    'raw_material_detail_id' => $detail->id,

                    // planned worker
                    'worker_id' => $validated['cutter_worker_id'],

                    'stage' => 'cutter',

                    'sequence' => 1,
                ]);

                /*
                |--------------------------------------------------------------------------
                | PROCESS : OVERDECK
                |--------------------------------------------------------------------------
                */

                RawMaterialDetailProcess::create([

                    'raw_material_detail_id' => $detail->id,

                    // planned worker
                    'worker_id' => $validated['overdeck_worker_id'],

                    'stage' => 'overdeck_tangan',

                    'sequence' => 2,
                ]);

                /*
                |--------------------------------------------------------------------------
                | FIRST DELIVERY
                |--------------------------------------------------------------------------
                |
                | Delivery nyata hanya ke cutter
                |
                */

                ProcessDelivery::create([

                    // 'raw_material_id' => $rawMaterial->id,

                    'raw_material_detail_process_id' => $cutterProcess->id,

                    // actual worker tujuan
                    'worker_id' => $validated['cutter_worker_id'],

                    'courier_id' => $validated['courier_id'],

                    'delivered_qty' => $validated['weights'][$i],

                    'delivered_unit' => 'kg',

                    'status' => 'pending',

                    'started_at' => null,

                    'arrived_at' => null,

                    'finished_at' => null,
                ]);
            }

        });

        return back()->with(
            'success',
            'Produksi Tahap 1 berhasil diupdate'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RawMaterial $rawMaterial)
    {
       
        if ($rawMaterial->status == 'process') {

            return back()->with(
                'error',
                'Produksi tahap 1 gagal dihapus karena sedang dalam proses pengiriman.'
            );
        }


        if ($rawMaterial->status == 'finished') {

            return back()->with(
                'error',
                'Produksi tahap 1 gagal dihapus karena telah selesai diproses.'
            );
        }


        $rawMaterial->delete();

        return back()->with(
            'success',
            'Produksi Tahap 1 berhasil dihapus.'
        );
    }

    private function calculateRemainingQty(
        $workerId,
        $stage
    ) {

        $processes = RawMaterialDetailProcess::with([
            'detail.variant.product',
            'progresses',
        ])
        ->where('worker_id', $workerId)
        ->where('stage', $stage)
        ->get();

        /*
        |------------------------------------------------
        | CUTTER
        |------------------------------------------------
        */

        if ($stage === 'cutter') {

            $totalRemaining = 0;

            $groups = $processes->groupBy(
                'detail.raw_material_id'
            );

            foreach ($groups as $rawMaterialId => $items) {

                $target = $items->sum(function ($process) {

                    return $this->calculateTargetQty(
                        $process
                    );
                });

                $confirmed =
                    $items->sum('qty_confirmed');

                if ($confirmed > 0) {

                    $completed = $confirmed;

                } else {

                    $completed = ProcessProgress::where(
                        'raw_material_id',
                        $rawMaterialId
                    )->sum('qty_progress');
                }

                $totalRemaining += max(
                    $target - $completed,
                    0
                );
            }

            return $totalRemaining;
        }

        /*
        |------------------------------------------------
        | STAGE SELAIN CUTTER
        |------------------------------------------------
        */

        $totalRemaining = 0;

        foreach ($processes as $process) {

            $target =
                $this->calculateTargetQty(
                    $process
                );

            if ($process->qty_confirmed > 0) {

                $completed =
                    $process->qty_confirmed;

            } else {

                $completed =
                    $process->progresses
                        ->sum('qty_progress');
            }

            $totalRemaining += max(
                $target - $completed,
                0
            );
        }

        return $totalRemaining;
    }

    private function calculateTargetQty($process)
    {
        $detail = $process->detail;

        /*
        |--------------------------------------------------------------------------
        | CUTTER
        |--------------------------------------------------------------------------
        */

        if ($process->stage === 'cutter') {

            return
                $detail->weight
                *
                $detail
                    ->variant
                    ->product
                    ->ratio_per_kg;
        }

        /*
        |--------------------------------------------------------------------------
        | PREVIOUS PROCESS
        |--------------------------------------------------------------------------
        */

        $previousProcess = RawMaterialDetailProcess::where(
            'raw_material_detail_id',
            $process->raw_material_detail_id
        )

        ->where('sequence', '<', $process->sequence)

        ->orderByDesc('sequence')

        ->first();

        if (!$previousProcess) {

            return 0;
        }

        return $previousProcess->qty_confirmed ?? 0;
    }

    private function generateTaskItems()
    {
        $capacity = 30;

        $items =
            ProductionPlanningItem::with([
                'productVariant.product'
            ])
            ->where(
                'remaining_kg',
                '>',
                0
            )
            ->orderBy(
                'priority_order'
            )
            ->get();

        $result = [];

        foreach ($items as $item) {

            if ($capacity <= 0) {
                break;
            }

            $takeKg = min(
                $item->remaining_kg,
                $capacity
            );

            $result[] = [

                'planning_item_id' => $item->id,

                'priority_order' => $item->priority_order,

                'product_name' =>
                    $item->productVariant->product->name,

                'size' =>
                    $item->productVariant->size,

                'remaining_kg' =>
                    $item->remaining_kg - $takeKg,

                'take_kg' =>
                    $takeKg,
            ];

            $capacity -= $takeKg;
        }

        return $result;
    }
}
