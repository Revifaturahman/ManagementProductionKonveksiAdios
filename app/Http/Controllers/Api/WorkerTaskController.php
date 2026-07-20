<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\ProcessDelivery;
use App\Models\ProcessProgress;
use App\Models\RawMaterialDetailProcess;
use App\Models\SemiProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerTaskController extends Controller
{
    public function getTasks(Request $request)
    {
        $workerId = $request
        ->user()
        ->worker_id;
        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'rawMaterialDetailProcess.worker',

            'rawMaterialDetailProcess.detail.rawMaterial',

            'rawMaterialDetailProcess.detail.variant.product',

            'rawMaterialDetailProcess.progresses',

        ])
        ->where('status', 'arrive')

        ->whereHas('rawMaterialDetailProcess', function ($q) use ($workerId) {

            $q->where('worker_id', $workerId);

        })

        ->orderBy('created_at', 'desc')

        ->get();

        /*
        |--------------------------------------------------------------------------
        | GROUP TASKS
        |--------------------------------------------------------------------------
        */

        $tasks = $deliveries->groupBy(function ($delivery) {

            return
                $delivery
                    ->rawMaterialDetailProcess
                    ->detail
                    ->raw_material_id
                . '-' .
                $delivery
                    ->rawMaterialDetailProcess
                    ->stage;
        })
        ->map(function ($group) {

            $first = $group->first();

            /*
            |--------------------------------------------------------------------------
            | TOTAL PROGRESS
            |--------------------------------------------------------------------------
            */

            $rawMaterialId = $first
                ->rawMaterialDetailProcess
                ->detail
                ->raw_material_id;

            $stage = $first
                ->rawMaterialDetailProcess
                ->stage;

            $totalProgress = ProcessProgress::where(
                'raw_material_id',
                $rawMaterialId
            )

            ->whereHas('rawMaterialProcess', function ($q) use ($stage) {

                $q->where('stage', $stage);
            })

            ->sum('qty_progress');

            /*
            |--------------------------------------------------------------------------
            | TOTAL TARGET
            |--------------------------------------------------------------------------
            */

            $totalTarget = $group->sum(function ($delivery) {

                return $this->calculateTargetQty(
                    $delivery->rawMaterialDetailProcess
                );
            });

            return [

                /*
                |--------------------------------------------------------------------------
                | TASK
                |--------------------------------------------------------------------------
                */

                'raw_material_id' => $first
                    ->rawMaterialDetailProcess
                    ->detail
                    ->rawMaterial
                    ->id,

                'stage' => $first
                    ->rawMaterialDetailProcess
                    ->stage,

                'stage_label' => $this->stageLabel(
                    $first
                        ->rawMaterialDetailProcess
                        ->stage
                ),

                'status' => $first->status,

                'task_created_at' => $first->created_at,

                /*
                |--------------------------------------------------------------------------
                | WORKER
                |--------------------------------------------------------------------------
                */

                'worker_id' => $first
                    ->worker
                    ->id ?? null,

                'worker_name' => $first
                    ->worker
                    ->name ?? null,

                /*
                |--------------------------------------------------------------------------
                | SUMMARY
                |--------------------------------------------------------------------------
                */

                'total_products' => $group->count(),

                'total_target' => $totalTarget,

                'total_progress' => $totalProgress,

                'remaining_qty' =>
                    $totalTarget - $totalProgress,

                /*
                |--------------------------------------------------------------------------
                | PRODUCTS
                |--------------------------------------------------------------------------
                */

                'products' => $group->map(function ($delivery) {

                    return [

                        'delivery_id' => $delivery->id,

                        'process_id' => $delivery
                            ->rawMaterialDetailProcess
                            ->id,

                        'product_name' => $delivery
                            ->rawMaterialDetailProcess
                            ->detail
                            ->variant
                            ->product
                            ->name,

                        'size' => $delivery
                            ->rawMaterialDetailProcess
                            ->detail
                            ->variant
                            ->size,

                        'weight' => $delivery
                            ->rawMaterialDetailProcess
                            ->detail
                            ->weight,

                        'qty_result' => $delivery
                            ->rawMaterialDetailProcess
                            ->detail
                            ->qty_result,

                        'qty_confirmed' => $delivery
                            ->rawMaterialDetailProcess
                            ->qty_confirmed,
                    ];
                })->values(),

                /*
                |--------------------------------------------------------------------------
                | LABEL
                |--------------------------------------------------------------------------
                */

                'task_label' =>
                    $this->stageLabel(
                        $first
                            ->rawMaterialDetailProcess
                            ->stage
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

    public function getTaskDetail(Request $request,
        $rawMaterialId,
        $stage
    )
    {
        $workerId = $request
        ->user()
        ->worker_id;
        $deliveries = ProcessDelivery::with([

            'worker',

            'courier',

            'rawMaterialDetailProcess.worker',

            'rawMaterialDetailProcess.detail.rawMaterial',

            'rawMaterialDetailProcess.detail.variant.product',

            'rawMaterialDetailProcess.progresses',

        ])
        ->where('status', 'arrive')

        ->whereHas('rawMaterialDetailProcess', function ($q)
            use ($workerId, $rawMaterialId, $stage) {

            $q->where('worker_id', $workerId)

            ->where('stage', $stage)

            ->whereHas('detail', function ($detail)
                use ($rawMaterialId) {

                $detail->where(
                    'raw_material_id',
                    $rawMaterialId
                );
            });
        })

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
            ->pluck('rawMaterialDetailProcess.id');

        $totalProgress = ProcessProgress::whereIn(
            'raw_material_detail_process_id',
            $processIds
        )->sum('qty_progress');

        /*
        |--------------------------------------------------------------------------
        | TOTAL TARGET
        |--------------------------------------------------------------------------
        */

        $totalTarget = $deliveries->sum(function ($delivery) {

            return $this->calculateTargetQty(

                $delivery->rawMaterialDetailProcess
            );
        });

        /*
        |--------------------------------------------------------------------------
        | PROGRESS HISTORY
        |--------------------------------------------------------------------------
        */

        $progressHistory = ProcessProgress::whereIn(
            'raw_material_detail_process_id',
            $processIds
        )
        ->orderByDesc('progress_date')
        ->get()
        ->map(function ($progress) {

            return [

                'id' => $progress->id,

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

                'raw_material_id' => $first
                    ->rawMaterialDetailProcess
                    ->detail
                    ->rawMaterial
                    ->id,

                'stage' => $first
                    ->rawMaterialDetailProcess
                    ->stage,

                'stage_label' => $this->stageLabel(
                    $first
                        ->rawMaterialDetailProcess
                        ->stage
                ),

                'is_cutting_stage' =>
                    $first
                        ->rawMaterialDetailProcess
                        ->stage === 'cutter',

                'status' => $first->status,

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

                    'id' => $first
                        ->worker
                        ->id ?? null,

                    'name' => $first
                        ->worker
                        ->name ?? null,

                    'phone' => $first
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
                    $totalTarget - $totalProgress,

                'progress_percentage' =>
                    $totalTarget > 0
                        ? round(($totalProgress / $totalTarget) * 100)
                        : 0,

                'is_finished' =>
                    ($totalTarget - $totalProgress) <= 0,
                /*
                |--------------------------------------------------------------------------
                | PRODUCTS
                |--------------------------------------------------------------------------
                */

                'products' => $deliveries->map(
                    function ($delivery) {

                    $detail = $delivery
                        ->rawMaterialDetailProcess
                        ->detail;

                    $productName = $detail
                        ->variant
                        ->product
                        ->name;

                    /*
                    |--------------------------------------------------------------------------
                    | ESTIMASI PCS
                    |--------------------------------------------------------------------------
                    */

                    $estimatedPcs = null;

                    if (
                        $delivery
                            ->rawMaterialDetailProcess
                            ->stage === 'cutter'
                    ) {

                        $pcsPerKg = $this->estimatePcsPerKg(
                            $productName
                        );

                        $estimatedPcs =
                            $detail->weight * $pcsPerKg;
                    }

                    return [

                        'delivery_id' => $delivery->id,

                        'process_id' =>
                            $delivery
                                ->rawMaterialDetailProcess
                                ->id,

                        'product_name' => $productName,

                        'size' =>
                            $detail
                                ->variant
                                ->size,

                        'weight' =>
                            $delivery
                                ->rawMaterialDetailProcess
                                ->stage === 'cutter'
                                    ? $detail->weight
                                    : null,

                        'estimated_pcs' =>
                            $estimatedPcs,

                        'qty' =>
                            $delivery->delivered_qty,

                        'qty_result' =>
                            $detail->qty_result,

                        'qty_confirmed' =>
                            $delivery
                                ->rawMaterialDetailProcess
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

            'raw_material_id' =>
                'required|exists:raw_materials,id',

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

        $processes = RawMaterialDetailProcess::with([
            'detail'
        ])

        ->where('stage', $request->stage)

        ->whereHas('detail', function ($q)
            use ($request) {

            $q->where(
                'raw_material_id',
                $request->raw_material_id
            );
        })

        ->get();

        if ($processes->isEmpty()) {

            return response()->json([

                'success' => false,

                'message' => 'Task tidak ditemukan',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIL PROCESS PERTAMA SEBAGAI ANCHOR
        |--------------------------------------------------------------------------
        */

        $anchorProcess = $processes->first();

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

            'raw_material_id' =>
                $request->raw_material_id,

            /*
            |--------------------------------------------------------------------------
            | PROCESS
            |--------------------------------------------------------------------------
            */

            'raw_material_detail_process_id' =>
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

            return $this->calculateTargetQty(
                $process
            );
        });

        /*
        |--------------------------------------------------------------------------
        | TOTAL PROGRESS
        |--------------------------------------------------------------------------
        */

        $totalProgress = ProcessProgress::where(

            'raw_material_id',

            $request->raw_material_id

        )->sum('qty_progress');

        /*
        |--------------------------------------------------------------------------
        | REMAINING
        |--------------------------------------------------------------------------
        */

        $remainingQty =
            $totalTarget - $totalProgress;

        return response()->json([

            'success' => true,

            'message' => 'Progress berhasil disimpan',

            'data' => [

                'total_target' =>
                    $totalTarget,

                'total_progress' =>
                    $totalProgress,

                'remaining_qty' =>
                    max($remainingQty, 0),

                'progress_percentage' =>
                    $totalTarget > 0
                        ? round(
                            ($totalProgress / $totalTarget) * 100
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