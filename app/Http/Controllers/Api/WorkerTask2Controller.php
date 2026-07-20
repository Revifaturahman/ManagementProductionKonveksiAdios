<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\ProcessDelivery;
use App\Models\ProcessProgress;
use App\Models\ProductionBatchDetailProcess;
use App\Models\SemiProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkerTask2Controller extends Controller
{
    public function getTasks(Request $request)
    {
        $workerId = $request->user()->worker_id;

        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'productionBatchDetailProcess.detail.batch',

            'productionBatchDetailProcess.detail.variant.product',

        ])

        ->where('status', 'arrive')

        ->whereNotNull(
            'production_batch_detail_process_id'
        )

        ->whereHas(
            'productionBatchDetailProcess',
            function ($q) use ($workerId) {

                $q->where(
                    'worker_id',
                    $workerId
                );
            }
        )

        ->orderByDesc('created_at')

        ->get();

        $tasks = $deliveries

            ->groupBy(function ($delivery) {

                return

                    $delivery
                        ->productionBatchDetailProcess
                        ->detail
                        ->production_batch_id

                    . '-'

                    .

                    $delivery
                        ->productionBatchDetailProcess
                        ->stage;
            })

            ->map(function ($group) {

                $first = $group->first();

                $process =
                    $first
                        ->productionBatchDetailProcess;

                $batchId =
                    $process
                        ->detail
                        ->production_batch_id;

                $stage =
                    $process
                        ->stage;

                /*
                |--------------------------------------------------------------------------
                | PROGRESS
                |--------------------------------------------------------------------------
                */

                $totalProgress = ProcessProgress::where(

                    'production_batch_id',

                    $batchId

                )

                ->whereHas(
                    'productionBatchProcess',
                    function ($q) use ($stage) {

                        $q->where(
                            'stage',
                            $stage
                        );
                    }
                )

                ->sum('qty_progress');

                /*
                |--------------------------------------------------------------------------
                | TARGET
                |--------------------------------------------------------------------------
                */

                $totalTarget =
                    $group->sum(
                        'delivered_qty'
                    );

                $remainingQty =
                    $totalTarget -
                    $totalProgress;

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | TASK
                    |--------------------------------------------------------------------------
                    */

                    'production_batch_id' =>
                        $batchId,

                    'stage' =>
                        $stage,

                    'stage_label' =>
                        $this->stageLabel($stage),

                    'status' =>
                        $first->status,

                    'task_created_at' =>
                        $first->created_at,

                    /*
                    |--------------------------------------------------------------------------
                    | WORKER
                    |--------------------------------------------------------------------------
                    */

                    'worker_id' =>
                        $first->worker->id ?? null,

                    'worker_name' =>
                        $first->worker->name ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | SUMMARY
                    |--------------------------------------------------------------------------
                    */

                    'total_products' =>
                        $group->count(),

                    'total_target' =>
                        $totalTarget,

                    'total_progress' =>
                        $totalProgress,

                    'remaining_qty' =>
                        max(
                            $remainingQty,
                            0
                        ),

                    'progress_percentage' =>

                        $totalTarget > 0

                            ? round(
                                (
                                    $totalProgress /
                                    $totalTarget
                                ) * 100
                            )

                            : 0,

                    'is_finished' =>
                        $remainingQty <= 0,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTS
                    |--------------------------------------------------------------------------
                    */

                    'products' =>

                        $group->map(
                            function ($delivery) {

                            $detail =
                                $delivery
                                    ->productionBatchDetailProcess
                                    ->detail;

                            return [

                                'delivery_id' =>
                                    $delivery->id,

                                'process_id' =>
                                    $delivery
                                        ->productionBatchDetailProcess
                                        ->id,

                                'product_name' =>
                                    $detail
                                        ->variant
                                        ->product
                                        ->name,

                                'size' =>
                                    $detail
                                        ->variant
                                        ->size,

                                'qty' =>
                                    $delivery
                                        ->delivered_qty,

                                'qty_confirmed' =>
                                    $delivery
                                        ->productionBatchDetailProcess
                                        ->qty_confirmed,
                            ];
                        })->values(),
                ];
            })

            ->values();

        return response()->json([

            'success' => true,

            'data' => $tasks,

        ]);
    }

    public function getTaskDetail(
        Request $request,
        $productionBatchId,
        $stage
    )
    {
        $workerId = $request
            ->user()
            ->worker_id;

        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'productionBatchDetailProcess.worker',

            'productionBatchDetailProcess.detail.batch',

            'productionBatchDetailProcess.detail.variant.product',

            'productionBatchDetailProcess.progresses',

        ])

        ->where('status', 'arrive')

        ->whereHas(
            'productionBatchDetailProcess',
            function ($q)
            use (
                $workerId,
                $productionBatchId,
                $stage
            ) {

                $q->where(
                    'worker_id',
                    $workerId
                )

                ->where(
                    'stage',
                    $stage
                )

                ->whereHas(
                    'detail',
                    function ($detail)
                    use ($productionBatchId) {

                        $detail->where(
                            'production_batch_id',
                            $productionBatchId
                        );
                    }
                );
            }
        )

        ->get();

        if ($deliveries->isEmpty()) {

            return response()->json([

                'success' => false,

                'message' => 'Task tidak ditemukan',

            ], 404);
        }

        $first = $deliveries->first();

        /*
        |--------------------------------------------------------------------------
        | TOTAL PROGRESS
        |--------------------------------------------------------------------------
        */

        $processIds = $deliveries
            ->pluck('productionBatchDetailProcess.id');

        $totalProgress = ProcessProgress::whereHas(
            'productionBatchProcess',
            function ($q) use ($stage) {

                $q->where(
                    'stage',
                    $stage
                );
            }
        )->whereIn(
            'production_batch_detail_process_id',
            $processIds
        )->sum('qty_progress');

        /*
        |--------------------------------------------------------------------------
        | TOTAL TARGET
        |--------------------------------------------------------------------------
        */

        $totalTarget = $deliveries->sum(
            'delivered_qty'
        );

        /*
        |--------------------------------------------------------------------------
        | PROGRESS HISTORY
        |--------------------------------------------------------------------------
        */

        $progressHistory = ProcessProgress::whereIn(
            'production_batch_detail_process_id',
            $processIds
        )
        ->whereHas(
            'productionBatchProcess',
            function ($q) use ($stage) {

                $q->where(
                    'stage',
                    $stage
                );
            }
        )
        ->orderByDesc('progress_date')
        ->get()
        ->map(function ($progress) {

            return [

                'id' =>
                    $progress->id,

                'progress_date' =>
                    $progress->progress_date,

                'qty_progress' =>
                    $progress->qty_progress,

                'notes' =>
                    $progress->notes,

                'created_at' =>
                    $progress->created_at,
            ];
        });

        return response()->json([

            'success' => true,

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | TASK INFO
                |--------------------------------------------------------------------------
                */

                'production_batch_id' =>

                    $first
                        ->productionBatchDetailProcess
                        ->detail
                        ->batch
                        ->id,

                'stage' =>

                    $first
                        ->productionBatchDetailProcess
                        ->stage,

                'stage_label' =>

                    $this->stageLabel(

                        $first
                            ->productionBatchDetailProcess
                            ->stage
                    ),

                'status' =>
                    $first->status,

                'task_created_at' =>
                    $first->created_at,

                'arrived_at' =>
                    $first->arrived_at,

                /*
                |--------------------------------------------------------------------------
                | WORKER
                |--------------------------------------------------------------------------
                */

                'worker' => [

                    'id' =>
                        $first
                            ->worker
                            ->id ?? null,

                    'name' =>
                        $first
                            ->worker
                            ->name ?? null,

                    'phone' =>
                        $first
                            ->worker
                            ->phone ?? null,
                ],

                /*
                |--------------------------------------------------------------------------
                | SUMMARY
                |--------------------------------------------------------------------------
                */

                'total_products' =>
                    $deliveries->count(),

                'total_target' =>
                    $totalTarget,

                'total_progress' =>
                    $totalProgress,

                'remaining_qty' =>
                    $totalTarget -
                    $totalProgress,

                'progress_percentage' =>

                    $totalTarget > 0

                        ? round(
                            (
                                $totalProgress /
                                $totalTarget
                            ) * 100
                        )

                        : 0,

                'is_finished' =>

                    (
                        $totalTarget -
                        $totalProgress
                    ) <= 0,

                /*
                |--------------------------------------------------------------------------
                | PRODUCTS
                |--------------------------------------------------------------------------
                */

                'products' =>

                    $deliveries->map(
                        function ($delivery) {

                        $detail = $delivery
                            ->productionBatchDetailProcess
                            ->detail;

                        return [

                            'delivery_id' =>
                                $delivery->id,

                            'process_id' =>
                                $delivery
                                    ->productionBatchDetailProcess
                                    ->id,

                            'product_name' =>
                                $detail
                                    ->variant
                                    ->product
                                    ->name,

                            'size' =>
                                $detail
                                    ->variant
                                    ->size,

                            'qty' =>
                                $delivery
                                    ->delivered_qty,

                            'qty_confirmed' =>
                                $delivery
                                    ->productionBatchDetailProcess
                                    ->qty_confirmed,
                        ];
                    }),

                /*
                |--------------------------------------------------------------------------
                | PROGRESS HISTORY
                |--------------------------------------------------------------------------
                */

                'progress_history' =>
                    $progressHistory,
            ],
        ]);
    }

    public function updateProgress(Request $request)
    {
        $request->validate([

            'production_batch_id' =>
                'required|exists:production_batches,id',

            'stage' =>
                'required|string',

            'qty_progress' =>
                'required|integer|min:1',

            'notes' =>
                'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA PROCESS TASK
        |--------------------------------------------------------------------------
        */

        $processes = ProductionBatchDetailProcess::with([
            'detail'
        ])

        ->where(
            'stage',
            $request->stage
        )

        ->whereHas(
            'detail',
            function ($q) use ($request) {

                $q->where(
                    'production_batch_id',
                    $request->production_batch_id
                );
            }
        )

        ->get();

        if ($processes->isEmpty()) {

            return response()->json([

                'success' => false,

                'message' => 'Task tidak ditemukan',

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | ANCHOR PROCESS
        |--------------------------------------------------------------------------
        */

        $anchorProcess =
            $processes->first();

        /*
        |--------------------------------------------------------------------------
        | INSERT PROGRESS
        |--------------------------------------------------------------------------
        */

        ProcessProgress::create([

            /*
            |--------------------------------------------------------------------------
            | TASK
            |--------------------------------------------------------------------------
            */

            'production_batch_id' =>
                $request->production_batch_id,

            /*
            |--------------------------------------------------------------------------
            | PROCESS
            |--------------------------------------------------------------------------
            */

            'production_batch_detail_process_id' =>
                $anchorProcess->id,

            /*
            |--------------------------------------------------------------------------
            | PROGRESS
            |--------------------------------------------------------------------------
            */

            'progress_date' => now(),

            'qty_progress' =>
                $request->qty_progress,

            'notes' =>
                $request->notes,
        ]);

        /*
        |--------------------------------------------------------------------------
        | TOTAL TARGET
        |--------------------------------------------------------------------------
        */

        $totalTarget = $processes->sum(function ($process) {

            return $process
                ->detail
                ->qty;
        });

        /*
        |--------------------------------------------------------------------------
        | TOTAL PROGRESS
        |--------------------------------------------------------------------------
        */

        $totalProgress = ProcessProgress::where(

            'production_batch_id',

            $request->production_batch_id

        )

        ->whereHas(
            'productionBatchProcess',
            function ($q) use ($request) {

                $q->where(
                    'stage',
                    $request->stage
                );
            }
        )

        ->sum('qty_progress');

        /*
        |--------------------------------------------------------------------------
        | REMAINING
        |--------------------------------------------------------------------------
        */

        $remainingQty =
            $totalTarget -
            $totalProgress;

        return response()->json([

            'success' => true,

            'message' => 'Progress berhasil disimpan',

            'data' => [

                'total_target' =>
                    $totalTarget,

                'total_progress' =>
                    $totalProgress,

                'remaining_qty' =>
                    max(
                        $remainingQty,
                        0
                    ),

                'progress_percentage' =>

                    $totalTarget > 0

                        ? round(
                            (
                                $totalProgress /
                                $totalTarget
                            ) * 100
                        )

                        : 0,

                'is_finished' =>

                    $remainingQty <= 0,
            ],
        ]);
    }

    private function stageLabel($stage)
    {
        return match ($stage) {

            'cutter' => 'Pemotong',

            'overdeck_tangan' => 'Overdeck Tangan',

            'tailor' => 'Penjahit',

            'obras' => 'Obras',

            'overdeck_bawah' => 'Overdeck Bawah',

            default => $stage,
        };
    }
}