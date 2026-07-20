<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\ProcessDelivery;
use App\Models\RawMaterialDetailProcess;
use App\Models\SemiProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDelivery1Controller extends Controller
{
    // Method Mengambil Task Kurir
    public function getCourierTasks(Request $request)
    {
        $courierId = $request->user()->id;
        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'rawMaterialDetailProcess.detail.rawMaterial',

            'rawMaterialDetailProcess.detail.variant.product',
        ])

        ->where('courier_id', $courierId)

        ->whereIn('status', [
            'pending',
        ])

        ->whereNotNull(
            'raw_material_detail_process_id'
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
                    . $delivery->rawMaterialDetailProcess->stage
                    . '-'
                    . $delivery
                        ->rawMaterialDetailProcess
                        ->detail
                        ->rawMaterial
                        ->id;
            })

            ->map(function ($group) {

                $first = $group->first();

                $process =
                    $first->rawMaterialDetailProcess;


                    // TIMER
                $rawMaterial =
                    $process
                        ->detail
                        ->rawMaterial;

                // Log::info([
                //     'raw_material_id' => $rawMaterial->id,
                //     'cycle_started_at' => $rawMaterial->cycle_started_at,
                // ]);

                $cycleStartedAt =
                    $rawMaterial->cycle_started_at;

                // $cycleStartedAt =
                //     $firstDeliveryInCycle?->started_at;
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

                        'cutter' => 'Pemotong',

                        'overdeck_tangan' => 'Overdeck Tangan',

                        'tailor' => 'Penjahit',

                        'obras' => 'Obras',

                        'overdeck_bawah' => 'Overdeck Bawah',

                        default => $process->stage,
                    },

                    'sequence' => $process->sequence,

                    'status' => $first->status,

                    'started_at' => $first->started_at,

                    'arrived_at' => $first->arrived_at,

                    'finished_at' => $first->finished_at,

                    'cycle_started_at' => $cycleStartedAt,

                    'time_limit_minutes' => 60,

                    'delivery_date' => $process
                        ->detail
                        ->rawMaterial
                        ->date,

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
                        $first->courier->id ?? null,

                    'courier_name' =>
                        $first->courier->name ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTS
                    |--------------------------------------------------------------------------
                    */

                    'products' => $group
                        ->map(function ($delivery) {

                        $process =
                            $delivery
                                ->rawMaterialDetailProcess;

                        return [

                            'delivery_id' =>
                                $delivery->id,

                            'variant_id' => $process
                                ->detail
                                ->variant
                                ->id,

                            'product_name' => $process
                                ->detail
                                ->variant
                                ->product
                                ->name,

                            'size' => $process
                                ->detail
                                ->variant
                                ->size,

                            'weight' => $process
                                ->detail
                                ->weight,

                            'qty_result' => $process
                                ->detail
                                ->qty_result,

                            'qty_confirmed' => $process
                                ->qty_confirmed,

                            'delivered_qty' =>
                                $delivery->delivered_qty,

                            'delivered_unit' =>
                                $delivery->delivered_unit,
                        ];
                    })->values(),

                    /*
                    |--------------------------------------------------------------------------
                    | SUMMARY
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

                    'rawMaterialDetailProcess.detail.rawMaterial'

                ])
                ->whereIn(
                    'id',
                    $request->delivery_ids
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
                    | RAW MATERIAL
                    |--------------------------------------------------------------------------
                    */

                    $rawMaterial =
                        $delivery
                            ->rawMaterialDetailProcess
                            ->detail
                            ->rawMaterial;

                    if (!$rawMaterial->cycle_started_at) {

                        $rawMaterial->update([

                            'cycle_started_at' => now(),
                        ]);
                    }

                    $rawMaterial->refresh();

                    $cycleStartedAt =
                        $rawMaterial->cycle_started_at;

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $rawMaterial->status
                        === 'pending'
                    ) {

                        $rawMaterial->update([

                            'status' => 'process',
                        ]);
                    }
                }
            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Pengiriman dimulai',

                'cycle_started_at' => $cycleStartedAt
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

                    'rawMaterialDetailProcess.detail.rawMaterial'

                ])
                ->whereIn(
                    'id',
                    $request->delivery_ids
                )
                ->whereNotNull(
                    'raw_material_detail_process_id'
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
                        | RAW MATERIAL
                        |--------------------------------------------------------------------------
                        */

                        $rawMaterial =
                            $delivery
                                ->rawMaterialDetailProcess
                                ->detail
                                ->rawMaterial;


                        /*
                        |--------------------------------------------------------------------------
                        | DETAIL
                        |--------------------------------------------------------------------------
                        */

                        $detail =
                            $delivery
                                ->rawMaterialDetailProcess
                                ->detail;

                        /*
                        |--------------------------------------------------------------------------
                        | PROCESS
                        |--------------------------------------------------------------------------
                        */

                        $process =
                            $delivery
                                ->rawMaterialDetailProcess;

                        /*
                        |--------------------------------------------------------------------------
                        | SEMI PRODUCT
                        |--------------------------------------------------------------------------
                        */

                        $semiProduct = SemiProduct::where(

                            'product_variant_id',

                            $detail->product_variant_id
                        )->first();

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE STOCK
                        |--------------------------------------------------------------------------
                        */

                        if ($semiProduct) {

                            $semiProduct->increment(

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

                                'rawMaterialDetailProcess.detail',

                                function ($q) use ($rawMaterial) {

                                    $q->where(
                                        'raw_material_id',
                                        $rawMaterial->id
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

                            $rawMaterial->update([

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
                        'rawMaterialDetailProcess'
                    ])->whereNotNull(
                        'raw_material_detail_process_id'
                    )->findOrFail(
                        $item['delivery_id']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $process = $delivery
                        ->rawMaterialDetailProcess;

                    if (!$process) {

                        throw new \Exception(
                            'RawMaterialDetailProcess not found'
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

                    // if ($process->stage !== 'cutter') {

                    //     $currentQty =
                    //         $process->qty_confirmed;

                    //     $targetQty =
                    //         $process->detail->qty;

                    //     $remainingQty =
                    //         $targetQty - $currentQty;

                    //     if (
                    //         $item['qty'] > $remainingQty
                    //     ) {

                    //         throw new \Exception(

                    //             'Qty melebihi target produksi'
                    //         );
                    //     }
                    // }

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

                        'rawMaterialDetailProcess.detail'

                    ])
                    
                    ->whereNotNull(
                        'raw_material_detail_process_id'
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
                        $delivery->rawMaterialDetailProcess;

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
                    | NEXT PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $nextProcess =
                        RawMaterialDetailProcess::where(

                            'raw_material_detail_id',

                            $currentProcess
                                ->raw_material_detail_id
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

                            // 'raw_material_id' =>
                            //     $currentProcess
                            //         ->detail
                            //         ->rawMaterial
                            //         ->id,

                            'raw_material_detail_process_id' =>
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

                            'raw_material_detail_process_id',

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

                        // 'raw_material_id' =>
                        //     $currentProcess
                        //         ->detail
                        //         ->rawMaterial
                        //         ->id,

                        'raw_material_detail_process_id' =>
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

            'rawMaterialDetailProcess.detail.rawMaterial',

            'rawMaterialDetailProcess.detail.variant.product',
        ])

        ->whereIn('status', [
            'arrive'
        ])

        ->whereNotNull(
        'raw_material_detail_process_id'
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
                    . $delivery->rawMaterialDetailProcess->stage
                    . '-'
                    . $delivery
                        ->rawMaterialDetailProcess
                        ->detail
                        ->rawMaterial
                        ->id;
            })

            ->map(function ($group) {

                $first = $group->first();

                $process =
                    $first->rawMaterialDetailProcess;

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

                        'cutter' => 'Pemotong',

                        'overdeck_tangan' => 'Overdeck Tangan',

                        'tailor' => 'Penjahit',

                        'obras' => 'Obras',

                        'overdeck_bawah' => 'Overdeck Bawah',

                        default => $process->stage,
                    },

                    'sequence' => $process->sequence,

                    'status' => $first->status,

                    'started_at' => $first->started_at,

                    'arrived_at' => $first->arrived_at,

                    'finished_at' => $first->finished_at,

                    'delivery_date' => $process
                        ->detail
                        ->rawMaterial
                        ->date,

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
                        $first->courier->id ?? null,

                    'courier_name' =>
                        $first->courier->name ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTS
                    |--------------------------------------------------------------------------
                    */

                    'products' => $group
                        ->map(function ($delivery) {

                        $process =
                            $delivery
                                ->rawMaterialDetailProcess;

                        $detail =
                            $process->detail;

                        $productName =
                            $detail
                                ->variant
                                ->product
                                ->name;

                        if ($process->stage === 'cutter') {

                            $pcsPerKg = $this->estimatePcsPerKg(
                                $productName
                            );

                            $targetQty =
                                $detail->weight * $pcsPerKg;

                        } else {

                            $targetQty =
                                (int) $delivery->delivered_qty;
                        }
                        return [

                            'delivery_id' =>
                                $delivery->id,

                            'variant_id' => $process
                                ->detail
                                ->variant
                                ->id,

                            'product_name' => $process
                                ->detail
                                ->variant
                                ->product
                                ->name,

                            'size' => $process
                                ->detail
                                ->variant
                                ->size,

                            'weight' => $process
                                ->detail
                                ->weight,

                            'qty_result' => $process
                                ->detail
                                ->qty_result,

                            'target_qty' =>
                                $targetQty,

                            'qty_confirmed' => $process
                                ->qty_confirmed,

                            'delivered_qty' =>
                                $delivery->delivered_qty,

                            'delivered_unit' =>
                                $delivery->delivered_unit,
                        ];
                    })->values(),

                    /*
                    |--------------------------------------------------------------------------
                    | SUMMARY
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

    private function estimatePcsPerKg($productName)
    {
        $productName = strtolower($productName);

        /*
        |--------------------------------------------------------------------------
        | KAOS ANAK
        |--------------------------------------------------------------------------
        */

        if (str_contains($productName, 'anak')) {

            return 8;
        }

        /*
        |--------------------------------------------------------------------------
        | KAOS PANJANG DEWASA
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($productName, 'panjang')
            &&
            str_contains($productName, 'dewasa')
        ) {

            return 3;
        }

        /*
        |--------------------------------------------------------------------------
        | KAOS PENDEK DEWASA
        |--------------------------------------------------------------------------
        */

        return 4;
    }

    private function calculateTargetQty($process)
    {
        $detail = $process->detail;

        $productName = $detail
            ->variant
            ->product
            ->name;

        /*
        |--------------------------------------------------------------------------
        | CUTTER
        |--------------------------------------------------------------------------
        */

        if ($process->stage === 'cutter') {

            $pcsPerKg = $this->estimatePcsPerKg(
                $productName
            );

            return $detail->weight * $pcsPerKg;
        }

        /*
        |--------------------------------------------------------------------------
        | STAGE SELAIN CUTTER
        |--------------------------------------------------------------------------
        */

        return $process->qty_confirmed ?? 0;
    }
}
