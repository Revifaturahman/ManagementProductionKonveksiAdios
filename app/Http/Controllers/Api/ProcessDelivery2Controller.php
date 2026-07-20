<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\FinishedProduct;
use App\Models\ProcessDelivery;
use App\Models\ProductionBatchDetailProcess;
use App\Models\RawMaterialDetailProcess;
use App\Models\SemiProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessDelivery2Controller extends Controller
{
    public function getCourierTasks(Request $request)
    {
        $courierId = $request->user()->id;
        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'productionBatchDetailProcess.detail.batch',

            'productionBatchDetailProcess.detail.variant.product',

        ])
        ->where('courier_id', $courierId)

        ->whereIn('status', [
            'pending',
        ])

        ->whereNotNull(
            'production_batch_detail_process_id'
        )

        ->latest()

        ->get();

        $tasks = $deliveries

            ->groupBy(function ($delivery) {

                return
                    $delivery->destination_type
                    . '-'
                    . $delivery->worker_id
                    . '-'
                    . $delivery
                        ->productionBatchDetailProcess
                        ->stage
                    . '-'
                    . $delivery
                        ->productionBatchDetailProcess
                        ->detail
                        ->batch
                        ->id;
            })

            ->map(function ($group) {

                $first = $group->first();

                $process =
                    $first->productionBatchDetailProcess;

                $batch =
                    $process
                        ->detail
                        ->batch;
                
                $cycleStartedAt =
                    $batch->cycle_started_at;
                /*
                |--------------------------------------------------------------------------
                | DESTINATION
                |--------------------------------------------------------------------------
                */

                $destinationType =
                    $first->destination_type;

                $destinationName = null;

                $destinationAddress = null;

                $destinationLatitude = null;

                $destinationLongitude = null;

                /*
                |--------------------------------------------------------------------------
                | WORKER
                |--------------------------------------------------------------------------
                */

                if ($destinationType === 'worker') {

                    $destinationName =
                        $first->worker->name ?? null;

                    $destinationAddress =
                        $first->worker->address ?? null;

                    $destinationLatitude =
                        $first->worker->latitude ?? null;

                    $destinationLongitude =
                        $first->worker->longitude ?? null;
                }

                /*
                |--------------------------------------------------------------------------
                | FACTORY
                |--------------------------------------------------------------------------
                */

                if ($destinationType === 'factory') {

                    $factory = CompanyProfile::first();

                    $destinationName =
                        $factory->name ?? null;

                    $destinationAddress =
                        $factory->address ?? null;

                    $destinationLatitude =
                        $factory->latitude ?? null;

                    $destinationLongitude =
                        $factory->longitude ?? null;
                }

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    'delivery_ids' => $group
                        ->pluck('id')
                        ->values(),

                    'stage' =>
                        $process->stage,

                    'stage_label' => match ($process->stage) {

                        'obras' => 'Obras',

                        'penjahit' => 'Penjahit',

                        'obras2' => 'Obras 2',

                        'penjahit2' => 'Penjahit 2',

                        'overdeck_bawah' =>
                            'Overdeck Bawah',

                        default =>
                            $process->stage,
                    },

                    'sequence' =>
                        $process->sequence,

                    'status' =>
                        $first->status,

                    'started_at' =>
                        $first->started_at,

                    'arrived_at' =>
                        $first->arrived_at,

                    'finished_at' =>
                        $first->finished_at,

                    'cycle_started_at' =>
                        $cycleStartedAt,

                    'time_limit_minutes' =>
                        120,
                    /*
                    |--------------------------------------------------------------------------
                    | BATCH
                    |--------------------------------------------------------------------------
                    */

                    'batch_id' =>
                        $process
                            ->detail
                            ->batch
                            ->id,

                    'delivery_date' =>
                        $process
                            ->detail
                            ->batch
                            ->date,

                    'type' =>
                        $process
                            ->detail
                            ->batch
                            ->type,

                    'type_label' => match (
                        $process
                            ->detail
                            ->batch
                            ->type
                    ) {

                        'oblong' =>
                            'Kaos Oblong',

                        'berkerah' =>
                            'Kaos Berkerah',

                        default =>
                            $process
                                ->detail
                                ->batch
                                ->type,
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | DESTINATION
                    |--------------------------------------------------------------------------
                    */

                    'destination_type' =>
                        $destinationType,

                    'destination_name' =>
                        $destinationName,

                    'destination_address' =>
                        $destinationAddress,

                    'destination_latitude' =>
                        $destinationLatitude,

                    'destination_longitude' =>
                        $destinationLongitude,

                    /*
                    |--------------------------------------------------------------------------
                    | WORKER
                    |--------------------------------------------------------------------------
                    */

                    'worker_id' =>
                        $first->worker->id ?? null,

                    'worker_name' =>
                        $first->worker->name ?? null,

                    'worker_role' =>
                        $first->worker->role ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | COURIER
                    |--------------------------------------------------------------------------
                    */

                    'courier_id' =>
                        $first->courier->id,

                    'courier_name' =>
                        $first->courier->name,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTS
                    |--------------------------------------------------------------------------
                    */

                    'products' => $group
                        ->map(function ($delivery) {

                        $process =
                            $delivery
                                ->productionBatchDetailProcess;

                        return [

                            'delivery_id' =>
                                $delivery->id,

                            'variant_id' =>
                                $process
                                    ->detail
                                    ->variant
                                    ->id,

                            'product_name' =>
                                $process
                                    ->detail
                                    ->variant
                                    ->product
                                    ->name,

                            'size' =>
                                $process
                                    ->detail
                                    ->variant
                                    ->size,

                            'qty' =>
                                $process
                                    ->detail
                                    ->qty,

                            'qty_confirmed' =>
                                $process
                                    ->qty_confirmed,

                            'delivered_qty' =>
                                $delivery
                                    ->delivered_qty,

                            'delivered_unit' =>
                                $delivery
                                    ->delivered_unit,
                        ];
                    }),

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL
                    |--------------------------------------------------------------------------
                    */

                    'total_products' =>
                        $group->count(),

                    'task_label' =>
                        strtoupper(
                            str_replace(
                                '_',
                                ' ',
                                $process->stage
                            )
                        )
                        . ' - '
                        . $group->count()
                        . ' Produk',
                ];
            })

            ->values();

        return response()->json([

            'success' => true,

            'data' => $tasks,
        ]);
    }

    public function startDelivery(Request $request)
    {
        try {

            $request->validate([

                'delivery_ids' => 'required|array',

                'delivery_ids.*' =>
                    'required|exists:process_deliveries,id',
            ]);

            $cycleStartedAt = null;

            DB::transaction(function () use ($request, &$cycleStartedAt) {

                $deliveries = ProcessDelivery::with([

                    'productionBatchDetailProcess.detail.batch'

                ])
                ->whereIn(
                    'id',
                    $request->delivery_ids
                )

                ->whereNotNull(
                    'production_batch_detail_process_id'
                )

                ->where('status', 'pending')

                ->get();

                foreach ($deliveries as $delivery) {

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    $delivery->update([

                        'status' => 'processing',

                        'started_at' => now(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTION BATCH
                    |--------------------------------------------------------------------------
                    */

                    $batch =
                        $delivery
                            ->productionBatchDetailProcess
                            ->detail
                            ->batch;

                    // TIMER
                    if (!$batch->cycle_started_at) {

                        $batch->update([

                            'cycle_started_at' => now(),
                        ]);
                    }

                    $cycleStartedAt = $batch->cycle_started_at;

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $batch->status
                        === 'pending'
                    ) {

                        $batch->update([

                            'status' => 'process',
                        ]);
                    }
                }
            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Pengiriman dimulai',

                'cycle_started_at' =>
                    $cycleStartedAt,
            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function arrive(Request $request)
    {
        try {

            $request->validate([

                'delivery_ids' => 'required|array',

                'delivery_ids.*' =>
                    'required|exists:process_deliveries,id',
            ]);

            DB::transaction(function () use ($request) {

                $deliveries = ProcessDelivery::with([

                    'productionBatchDetailProcess.detail.batch'

                ])
                ->whereIn(
                    'id',
                    $request->delivery_ids
                )

                ->whereNotNull(
                    'production_batch_detail_process_id'
                )

                ->where('status', 'processing')

                ->get();

                foreach ($deliveries as $delivery) {

                    /*
                    |--------------------------------------------------------------------------
                    | RETURN FACTORY
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $delivery->destination_type
                        === 'factory'
                    ) {

                        $delivery->update([

                            'status' => 'finished',

                            'arrived_at' => now(),

                            'finished_at' => now(),
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | BATCH
                        |--------------------------------------------------------------------------
                        */

                        $batch =
                            $delivery
                                ->productionBatchDetailProcess
                                ->detail
                                ->batch;

                        /*
                        |--------------------------------------------------------------------------
                        | DETAIL
                        |--------------------------------------------------------------------------
                        */

                        $detail =
                            $delivery
                                ->productionBatchDetailProcess
                                ->detail;

                        /*
                        |--------------------------------------------------------------------------
                        | PROCESS
                        |--------------------------------------------------------------------------
                        */

                        $process =
                            $delivery
                                ->productionBatchDetailProcess;

                        /*
                        |--------------------------------------------------------------------------
                        | FINISHED PRODUCT
                        |--------------------------------------------------------------------------
                        */

                        $finishedProduct =
                            FinishedProduct::where(

                                'product_variant_id',

                                $detail->product_variant_id
                            )->first();

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE STOCK
                        |--------------------------------------------------------------------------
                        */

                        if ($finishedProduct) {

                            $finishedProduct->increment(

                                'qty',

                                $process->qty_confirmed
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | CEK MASIH ADA DELIVERY BELUM FINISHED?
                        |--------------------------------------------------------------------------
                        */

                        $unfinishedDeliveries =
                            ProcessDelivery::whereHas(

                                'productionBatchDetailProcess.detail',

                                function ($q) use ($batch) {

                                    $q->where(
                                        'production_batch_id',
                                        $batch->id
                                    );
                                }
                            )
                            ->where(
                                'status',
                                '!=',
                                'finished'
                            )
                            ->exists();

                        /*
                        |--------------------------------------------------------------------------
                        | JIKA SEMUA FINISHED
                        |--------------------------------------------------------------------------
                        */

                        if (!$unfinishedDeliveries) {

                            $batch->update([

                                'status' => 'finished',
                            ]);
                        }

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NORMAL WORKER
                    |--------------------------------------------------------------------------
                    */

                    $delivery->update([

                        'status' => 'arrive',

                        'arrived_at' => now(),
                    ]);
                }
            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Kurir telah sampai tujuan',
            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function updateResult(Request $request)
    {
        try {

            $request->validate([

                'results' => 'required|array',

                'results.*.delivery_id' =>
                    'required|exists:process_deliveries,id',

                'results.*.qty' =>
                    'required|numeric|min:1',
            ]);

            DB::transaction(function () use ($request) {

                foreach ($request->results as $item) {

                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    $delivery = ProcessDelivery::with([

                        'productionBatchDetailProcess'

                    ])

                    ->whereNotNull(
                        'production_batch_detail_process_id'
                    )

                    ->findOrFail(
                        $item['delivery_id']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $process = $delivery
                        ->productionBatchDetailProcess;

                    if (!$process) {

                        throw new \Exception(

                            'ProductionBatchDetailProcess not found'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE RESULT
                    |--------------------------------------------------------------------------
                    |
                    | qty_confirmed
                    | = total hasil kerja aktual
                    |
                    */

                    $delivery->update([

                        'received_qty' => $item['qty'],

                        'received_unit' => 'pcs',
                    ]);

                    $process->increment(

                        'qty_confirmed',

                        $item['qty']
                    );
                }
            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Hasil pekerjaan berhasil diupdate',
            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function nextProcess(Request $request)
    {
        try {

            $request->validate([

                'delivery_ids' => 'required|array',

                'delivery_ids.*' =>
                    'required|exists:process_deliveries,id',
            ]);

            DB::transaction(function () use ($request) {

                foreach ($request->delivery_ids as $deliveryId) {

                    /*
                    |--------------------------------------------------------------------------
                    | CURRENT DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    $delivery = ProcessDelivery::with([

                        'productionBatchDetailProcess.detail'

                    ])

                    ->whereNotNull(
                        'production_batch_detail_process_id'
                    )

                    ->findOrFail($deliveryId);

                    /*
                    |--------------------------------------------------------------------------
                    | SUDAH FINISHED?
                    |--------------------------------------------------------------------------
                    */

                    if ($delivery->status === 'finished') {

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CURRENT PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $currentProcess =
                        $delivery->productionBatchDetailProcess;

                    if (!$currentProcess) {

                        throw new \Exception(
                            'Process tidak ditemukan'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FINISH DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    $delivery->update([

                        'status' => 'finished',

                        'finished_at' => now(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | REMAINING QTY
                    |--------------------------------------------------------------------------
                    */

                    $remainingQty = max(
                        $delivery->delivered_qty -
                        $delivery->received_qty,
                        0
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | MASIH ADA SISA DI WORKER SEKARANG
                    |--------------------------------------------------------------------------
                    */

                    if ($remainingQty > 0) {

                        ProcessDelivery::create([

                            'production_batch_detail_process_id' =>
                                $currentProcess->id,

                            'worker_id' =>
                                $currentProcess->worker_id,

                            'courier_id' =>
                                $request->user()->id,

                            'delivered_qty' =>
                                $remainingQty,

                            'delivered_unit' =>
                                $delivery->delivered_unit,

                            'status' => 'arrive',

                            'type' => 'process',

                            'destination_type' => 'worker',

                            'started_at' => null,

                            'arrived_at' => now(),

                            'finished_at' => null,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NEXT PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $nextProcess =
                        ProductionBatchDetailProcess::where(

                            'production_batch_detail_id',

                            $currentProcess
                                ->production_batch_detail_id
                        )
                        ->where(

                            'sequence',

                            $currentProcess->sequence + 1
                        )
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | JIKA ADA NEXT PROCESS
                    |--------------------------------------------------------------------------
                    */

                    if ($nextProcess) {

                        /*
                        |--------------------------------------------------------------------------
                        | CREATE NEXT DELIVERY
                        |--------------------------------------------------------------------------
                        */

                        ProcessDelivery::create([

                            'production_batch_detail_process_id' =>
                                $nextProcess->id,

                            /*
                            |--------------------------------------------------------------------------
                            | DELIVERY TYPE
                            |--------------------------------------------------------------------------
                            */

                            'type' => 'process',

                            'destination_type' => 'worker',

                            /*
                            |--------------------------------------------------------------------------
                            | DESTINATION
                            |--------------------------------------------------------------------------
                            */

                            'worker_id' =>
                                $nextProcess->worker_id,

                            /*
                            |--------------------------------------------------------------------------
                            | COURIER
                            |--------------------------------------------------------------------------
                            */

                            'courier_id' =>
                                $request->user()->id,

                            /*
                            |--------------------------------------------------------------------------
                            | ITEM
                            |--------------------------------------------------------------------------
                            */

                            'delivered_qty' =>
                                $delivery->received_qty,

                            'delivered_unit' =>
                                $delivery->received_unit,

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

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | TIDAK ADA NEXT PROCESS
                    |--------------------------------------------------------------------------
                    |
                    | RETURN TO FACTORY
                    |
                    */

                    $existingReturnFactory =
                        ProcessDelivery::where(

                            'production_batch_detail_process_id',

                            $currentProcess->id
                        )
                        ->where(

                            'type',

                            'return_factory'
                        )
                        ->whereIn('status', [

                            'pending',

                            'processing',

                            'arrive',
                        ])
                        ->exists();

                    /*
                    |--------------------------------------------------------------------------
                    | JIKA SUDAH ADA RETURN DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    if ($existingReturnFactory) {

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE RETURN FACTORY DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    ProcessDelivery::create([

                        'production_batch_detail_process_id' =>
                            $currentProcess->id,

                        /*
                        |--------------------------------------------------------------------------
                        | DELIVERY TYPE
                        |--------------------------------------------------------------------------
                        */

                        'type' => 'return_factory',

                        'destination_type' => 'factory',

                        /*
                        |--------------------------------------------------------------------------
                        | WORKER
                        |--------------------------------------------------------------------------
                        */

                        'worker_id' =>
                            $delivery->worker_id,

                        /*
                        |--------------------------------------------------------------------------
                        | COURIER
                        |--------------------------------------------------------------------------
                        */

                        'courier_id' =>
                            $request->user()->id,

                        /*
                        |--------------------------------------------------------------------------
                        | ITEM
                        |--------------------------------------------------------------------------
                        */

                        'delivered_qty' =>
                            $delivery->received_qty,

                        'delivered_unit' =>
                            $delivery->received_unit,

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
            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Barang berhasil diproses ke tahap berikutnya',
            ]);

        } catch (\Exception $e) {
            // dd($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function getArrivedTasks()
    {
        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'productionBatchDetailProcess.detail.batch',

            'productionBatchDetailProcess.detail.variant.product',
        ])

        ->whereIn('status', [
            'arrive'
        ])

        ->whereNotNull(
            'production_batch_detail_process_id'
        )

        ->latest()

        ->get();

        $tasks = $deliveries

            /*
            |--------------------------------------------------------------------------
            | GROUPING TASK
            |--------------------------------------------------------------------------
            */

            ->groupBy(function ($delivery) {

                return
                    $delivery->worker_id
                    . '-'
                    . $delivery
                        ->productionBatchDetailProcess
                        ->stage
                    . '-'
                    . $delivery
                        ->productionBatchDetailProcess
                        ->detail
                        ->batch
                        ->id;
            })

            ->map(function ($group) {

                $first = $group->first();

                $process =
                    $first->productionBatchDetailProcess;

                /*
                |--------------------------------------------------------------------------
                | DESTINATION
                |--------------------------------------------------------------------------
                */

                $destinationType =
                    $first->destination_type;

                $destinationName = null;

                $destinationAddress = null;

                $destinationLatitude = null;

                $destinationLongitude = null;

                /*
                |--------------------------------------------------------------------------
                | WORKER
                |--------------------------------------------------------------------------
                */

                if ($destinationType === 'worker') {

                    $destinationName =
                        $first->worker->name ?? null;

                    $destinationAddress =
                        $first->worker->address ?? null;

                    $destinationLatitude =
                        $first->worker->latitude ?? null;

                    $destinationLongitude =
                        $first->worker->longitude ?? null;
                }

                /*
                |--------------------------------------------------------------------------
                | FACTORY
                |--------------------------------------------------------------------------
                */

                if ($destinationType === 'factory') {

                    $factory = CompanyProfile::first();

                    $destinationName =
                        $factory->name ?? null;

                    $destinationAddress =
                        $factory->address ?? null;

                    $destinationLatitude =
                        $factory->latitude ?? null;

                    $destinationLongitude =
                        $factory->longitude ?? null;
                }

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    'delivery_ids' => $group
                        ->pluck('id')
                        ->values(),

                    'stage' => $process->stage,

                    'stage_label' => match ($process->stage) {

                        'obras' => 'Obras',

                        'penjahit' => 'Penjahit',

                        'obras2' => 'Obras 2',

                        'penjahit2' => 'Penjahit 2',

                        'overdeck_bawah' => 'Overdeck Bawah',

                        default => $process->stage,
                    },

                    'sequence' => $process->sequence,

                    'status' => $first->status,

                    'started_at' => $first->started_at,

                    'arrived_at' => $first->arrived_at,

                    'finished_at' => $first->finished_at,

                    /*
                    |--------------------------------------------------------------------------
                    | BATCH
                    |--------------------------------------------------------------------------
                    */

                    'batch_id' =>
                        $process
                            ->detail
                            ->batch
                            ->id,

                    'delivery_date' =>
                        $process
                            ->detail
                            ->batch
                            ->date,

                    'type' =>
                        $process
                            ->detail
                            ->batch
                            ->type,

                    'type_label' => match (
                        $process
                            ->detail
                            ->batch
                            ->type
                    ) {

                        'oblong' => 'Kaos Oblong',

                        'berkerah' => 'Kaos Berkerah',

                        default =>
                            $process
                                ->detail
                                ->batch
                                ->type,
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | DESTINATION
                    |--------------------------------------------------------------------------
                    */

                    'destination_type' =>
                        $destinationType,

                    'destination_name' =>
                        $destinationName,

                    'destination_address' =>
                        $destinationAddress,

                    'destination_latitude' =>
                        $destinationLatitude,

                    'destination_longitude' =>
                        $destinationLongitude,

                    /*
                    |--------------------------------------------------------------------------
                    | WORKER
                    |--------------------------------------------------------------------------
                    */

                    'worker_id' =>
                        $first->worker->id,

                    'worker_name' =>
                        $first->worker->name,

                    'worker_role' =>
                        $first->worker->role,

                    /*
                    |--------------------------------------------------------------------------
                    | COURIER
                    |--------------------------------------------------------------------------
                    */

                    'courier_id' =>
                        $first->courier->id,

                    'courier_name' =>
                        $first->courier->name,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTS
                    |--------------------------------------------------------------------------
                    */

                    'products' => $group
                        ->map(function ($delivery) {

                        $process =
                            $delivery
                                ->productionBatchDetailProcess;

                        return [

                            'delivery_id' =>
                                $delivery->id,

                            'variant_id' =>
                                $process
                                    ->detail
                                    ->variant
                                    ->id,

                            'product_name' =>
                                $process
                                    ->detail
                                    ->variant
                                    ->product
                                    ->name,

                            'size' =>
                                $process
                                    ->detail
                                    ->variant
                                    ->size,

                            'qty' =>
                                $process
                                    ->detail
                                    ->qty,

                            'qty_confirmed' =>
                                $process
                                    ->qty_confirmed,

                            'target_qty' => (int) $delivery->delivered_qty,

                            'delivered_qty' =>
                                $delivery
                                    ->delivered_qty,

                            'delivered_unit' =>
                                $delivery
                                    ->delivered_unit,
                        ];
                    }),

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL
                    |--------------------------------------------------------------------------
                    */

                    'total_products' =>
                        $group->count(),

                    'task_label' =>
                        strtoupper($process->stage)
                        . ' - '
                        . $group->count()
                        . ' Produk',
                ];
            })

            ->values();

        return response()->json([

            'success' => true,

            'data' => $tasks,
        ]);
    }
}