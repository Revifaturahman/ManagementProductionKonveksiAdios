<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use App\Models\ProcessDelivery;
use App\Models\ProcessProgress;
use App\Models\ProductCategory;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchDetail;
use App\Models\ProductionBatchDetailProcess;
use App\Models\ProductVariant;
use App\Models\SemiProduct;
use App\Models\User;
use App\Models\Worker;
use App\Services\ProductionBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(
        Request $request,
        ProductionBatchService $service
    )
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUCTION BATCHES
        |--------------------------------------------------------------------------
        */

        $query = ProductionBatch::with([
            'courier',
            'details.variant.product',
            'details.processes'
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

        $productionBatches = $query
            ->latest()
            ->get();


        $couriers = User::where('role', 'courier')
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
            ->get();

        /*
        |--------------------------------------------------------------------------
        | WORKERS
        |--------------------------------------------------------------------------
        */

        $obras1Workers = Worker::where('role', 'obras')
            ->get()
            ->map(function ($worker) {
                $worker->remaining_qty =
                    $this->calculateProductionRemainingQty(
                        $worker->id,
                        ['obras', 'obras2']
                    );

                $worker->active_type =
                    $this->getWorkerActiveType($worker->id);

                $worker->next_process =
                    $this->getWorkerNextProcess($worker->id);

                return $worker;
            })
            ->sortBy('remaining_qty')
            ->values();

        $obras2Workers = Worker::where(
            'role',
            'obras'
        )
            ->get()
            ->map(function ($worker) {
                $worker->remaining_qty =
                    $this->calculateProductionRemainingQty(
                        $worker->id,
                        ['obras', 'obras2']
                    );

                $worker->active_type =
                    $this->getWorkerActiveType($worker->id);

                $worker->next_process =
                    $this->getWorkerNextProcess($worker->id);

                return $worker;
            })
            ->sortBy('remaining_qty')
            ->values();

        $tailor1Workers = Worker::where('role', 'tailor')
        ->get()
        ->map(function ($worker) {
            $worker->remaining_qty =
                $this->calculateProductionRemainingQty(
                    $worker->id,
                    ['penjahit', 'penjahit2']
                );

            $worker->active_type =
                $this->getWorkerActiveType($worker->id);

            $worker->next_process =
                $this->getWorkerNextProcess($worker->id);

            return $worker;
        })
        ->sortBy('remaining_qty')
        ->values();

        $tailor2Workers = Worker::where(
            'role',
            'tailor'
        )
        ->get()
        ->map(function ($worker) {
            $worker->remaining_qty =
                $this->calculateProductionRemainingQty(
                    $worker->id,
                    ['penjahit', 'penjahit2']
                );

            $worker->active_type =
                $this->getWorkerActiveType($worker->id);

            $worker->next_process =
                $this->getWorkerNextProcess($worker->id);

            return $worker;
        })
        ->sortBy('remaining_qty')
        ->values();

        return view('production.productionBatch', [

            'productionBatches' =>
                $productionBatches,

            'couriers' => User::where('role', 'courier')
                ->where('is_active', 1)
                ->get(),

            'variants' => ProductVariant::with([
                'product.category',
                'semiProduct'
            ])
            ->get()
            ->groupBy(function ($item) {

                return str_contains(
                    strtolower(
                        $item->product->category->name
                    ),
                    'oblong'
                )
                    ? 'oblong'
                    : 'berkerah';
            }),

            'overdeck_bottoms' => Worker::where(
                'role',
                'overdeck'
            )
            ->where(
                'overdeck_type',
                'bawah'
            )
            ->get()
            ->map(function ($worker) {

                $worker->remaining_qty =
                    $this->calculateProductionRemainingQty(
                        $worker->id,
                        [
                            'overdeck_bawah'
                        ]
                    );

                $worker->active_type =
                    $this->getWorkerActiveType(
                        $worker->id
                    );

                $worker->next_process =
                    $this->getWorkerNextProcess(
                        $worker->id
                    );

                return $worker;
            }),

            'couriers' => $couriers,

            'obras1Workers' =>
                $obras1Workers,

            'obras2Workers' =>
                $obras2Workers,

            'tailor1Workers' =>
                $tailor1Workers,

            'tailor2Workers' =>
                $tailor2Workers,

            'variants_flat' =>
                ProductVariant::with([
                    'product',
                    'semiProduct'
                ])->get(),

            'workers' =>
                Worker::all(),

            'categories' =>
                ProductCategory::all(),

            'suggestions' =>
                $service
                    ->generateStageTwoSuggestions(),
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
        if ($request->type == 'oblong') {

            $request->validate([

                'obras_id' => 'required',

                'penjahit_id' => 'required',

                'obras2_id' => 'required',

                'overdek_id' => 'required',

            ]);
        }if ($request->type == 'berkerah') {

            $request->validate([

                'penjahit_id' => 'required',

                'obras_id' => 'required',

                'penjahit2_id' => 'required',

                'overdek_id' => 'required',

            ]);
        }
        $request->validate([

            'courier_id' => 'required',

            'type' => 'required',

            'date' => 'required|date',

            'product_variant_ids' => 'required|array|min:1',

            'product_variant_ids.*' => 'required',

            'qty.*' => 'required|numeric|min:1',

        ]);

        // ================= VALIDASI STOK =================
        $errors = [];

        foreach ($request->product_variant_ids as $i => $variantId) {

            $qtyInput = $request->qty[$i];

            $semi = SemiProduct::where('product_variant_id', $variantId)->first();

            if (!$semi) {
                $errors[] = "Variant ID {$variantId} tidak memiliki stok";
                continue;
            }

            if ($semi->qty < $qtyInput) {
                $errors[] = "Stok untuk variant ID {$variantId} tidak cukup (stok: {$semi->qty}, request: {$qtyInput})";
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        // ================= PROSES =================
        DB::transaction(function () use ($request) {

        /*
        |--------------------------------------------------------------------------
        | UPDATE ROUTING AKTIF
        |--------------------------------------------------------------------------
        |
        | Semua process yang belum pernah dibuat delivery
        | akan mengikuti worker terbaru yang dipilih admin.
        |
        */

        $stages = $this->generateWorkflow($request);

        for ($i = 0; $i < count($stages) - 1; $i++) {

            $currentWorkerId = $stages[$i]['worker_id'];

            $nextWorkerId = $stages[$i + 1]['worker_id'];

            $nextStage = $stages[$i + 1]['stage'];

            ProductionBatchDetailProcess::where(
                'stage',
                $nextStage
            )
            ->where(
                'worker_id',
                '!=',
                $nextWorkerId
            )
            ->whereHas(
                'detail.processes',
                function ($q) use ($currentWorkerId) {

                    $q->where(
                        'worker_id',
                        $currentWorkerId
                    );
                }
            )
            ->whereDoesntHave('deliveries')
            ->update([

                'worker_id' => $nextWorkerId
            ]);
        }

            $batch = ProductionBatch::create([
                'courier_id' => $request->courier_id,
                'type' => $request->type,
                'date'       => $request->date,
            ]);

            foreach ($request->product_variant_ids as $i => $variantId) {

                $qtyInput = $request->qty[$i];

                $semi = SemiProduct::where('product_variant_id', $variantId)
                            ->lockForUpdate()
                            ->first();

                $semi->decrement('qty', $qtyInput);

                $detail = ProductionBatchDetail::create([

                    'production_batch_id' => $batch->id,

                    'product_variant_id' => $variantId,

                    'qty' => $qtyInput,

                ]);
                $stages = $this->generateWorkflow($request);

                $firstProcess = null;

                foreach ($stages as $index => $stage) {

                    $process = ProductionBatchDetailProcess::create([

                        'production_batch_detail_id' => $detail->id,

                        'worker_id' => $stage['worker_id'],

                        'stage' => $stage['stage'],

                        'sequence' => $index + 1,

                    ]);

                    // simpan proses pertama
                    if ($index == 0) {
                        $firstProcess = $process;
                    }
                }
                if ($firstProcess) {

                    ProcessDelivery::create([

                        'production_batch_detail_process_id'
                            => $firstProcess->id,

                        'worker_id'
                            => $firstProcess->worker_id,

                        'courier_id'
                            => $request->courier_id,

                        'delivered_qty'
                            => $qtyInput,

                        'delivered_unit'
                            => 'pcs',

                        'status'
                            => 'pending',

                        'type'
                            => 'process',

                        'destination_type'
                            => 'worker',

                    ]);
                }
            }

        });

        return back()->with('success', 'Produksi Tahap 2 Berhasil Ditambahkan');
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
    public function update(Request $request, $id)
    {
        // dd($request->all());
        // ================= VALIDASI WORKFLOW =================
        if ($request->type == 'oblong') {

            $request->validate([

                'obras_id' => 'required',

                'penjahit_id' => 'required',

                'obras2_id' => 'required',

                'overdek_id' => 'required',

            ]);
        }

        if ($request->type == 'berkerah') {

            $request->validate([

                'penjahit_id' => 'required',

                'obras_id' => 'required',

                'penjahit2_id' => 'required',

                'overdek_id' => 'required',

            ]);
        }

        // ================= VALIDASI UMUM =================
        $request->validate([

            'courier_id' => 'required',

            'type' => 'required',

            'date' => 'required|date',

            'product_variant_ids' => 'required|array|min:1',

            'product_variant_ids.*' => 'required',

            'qty.*' => 'required|numeric|min:1',

        ]);

        // ================= AMBIL DATA =================
        $batch = ProductionBatch::with([
            'details.processes'
        ])->findOrFail($id);

        // ================= CEK STATUS =================
        if ($batch->status !== 'pending') {

            return back()->withErrors([
                'Produksi Tahap 2 Tidak Bisa Diubah Karena Sudah Diproses'
            ]);
        }

        try {

            DB::transaction(function () use ($request, $batch) {

                // ================= BALIKIN STOK LAMA =================
                foreach ($batch->details as $detail) {

                    $semi = SemiProduct::where(
                        'product_variant_id',
                        $detail->product_variant_id
                    )
                    ->lockForUpdate()
                    ->first();

                    if ($semi) {

                        $semi->increment(
                            'qty',
                            $detail->qty
                        );
                    }
                }

                // ================= VALIDASI STOK BARU =================
                foreach ($request->product_variant_ids as $i => $variantId) {

                    $qtyInput = $request->qty[$i];

                    $semi = SemiProduct::where(
                        'product_variant_id',
                        $variantId
                    )
                    ->lockForUpdate()
                    ->first();

                    if (!$semi) {

                        throw new \Exception(
                            "Variant tidak memiliki stok"
                        );
                    }

                    if ($semi->qty < $qtyInput) {

                        throw new \Exception(
                            "Stok tidak cukup untuk variant ID {$variantId}"
                        );
                    }
                }

                // ================= HAPUS DELIVERY & PROCESS LAMA =================
                foreach ($batch->details as $detail) {

                    foreach ($detail->processes as $process) {

                        ProcessDelivery::where(
                            'production_batch_detail_process_id',
                            $process->id
                        )->delete();
                    }

                    $detail->processes()->delete();
                }

                // ================= HAPUS DETAIL LAMA =================
                $batch->details()->delete();

                // ================= UPDATE HEADER =================
                $batch->update([

                    'courier_id' => $request->courier_id,

                    'type' => $request->type,

                    'date' => $request->date,

                ]);

                // ================= GENERATE WORKFLOW =================
                $stages = $this->generateWorkflow($request);

                // ================= INSERT DETAIL BARU =================
                foreach ($request->product_variant_ids as $i => $variantId) {

                    $qtyInput = $request->qty[$i];

                    $semi = SemiProduct::where(
                        'product_variant_id',
                        $variantId
                    )
                    ->lockForUpdate()
                    ->first();

                    // ================= KURANGI STOK =================
                    $semi->decrement(
                        'qty',
                        $qtyInput
                    );

                    // ================= CREATE DETAIL =================
                    $detail = ProductionBatchDetail::create([

                        'production_batch_id' => $batch->id,

                        'product_variant_id' => $variantId,

                        'qty' => $qtyInput,

                    ]);

                    // ================= CREATE PROCESS =================
                    $firstProcess = null;

                    foreach ($stages as $index => $stage) {

                        $process =
                            ProductionBatchDetailProcess::create([

                            'production_batch_detail_id'
                                => $detail->id,

                            'worker_id'
                                => $stage['worker_id'],

                            'stage'
                                => $stage['stage'],

                            'sequence'
                                => $index + 1,

                        ]);

                        // simpan process pertama
                        if ($index == 0) {

                            $firstProcess = $process;
                        }
                    }

                    // ================= CREATE DELIVERY PERTAMA =================
                    if ($firstProcess) {

                        ProcessDelivery::create([

                            'production_batch_detail_process_id'
                                => $firstProcess->id,

                            'worker_id'
                                => $firstProcess->worker_id,

                            'courier_id'
                                => $request->courier_id,

                            'delivered_qty'
                                => $qtyInput,

                            'delivered_unit'
                                => 'pcs',

                            'status'
                                => 'pending',

                            'type'
                                => 'process',

                            'destination_type'
                                => 'worker',

                        ]);
                    }
                }
            });

            return back()->with(
                'success',
                'Produksi Tahap 2 Berhasil Diubah'
            );

        } catch (\Exception $e) {

            return back()
                ->withErrors([
                    $e->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $batch = ProductionBatch::with([
            'details.processes'
        ])->findOrFail($id);

        // ================= CEK STATUS =================
        if ($batch->status !== 'pending') {

            return back()
                ->withErrors([
                    'Produksi Tahap 2 Tidak Bisa Dihapus Karena Sudah Diproses'
                ])
                ->with('error_id', $batch->id);
        }

        DB::transaction(function () use ($batch) {

            // ================= BALIKIN STOCK =================
            foreach ($batch->details as $detail) {

                $semi = SemiProduct::where(
                    'product_variant_id',
                    $detail->product_variant_id
                )
                ->lockForUpdate()
                ->first();

                if ($semi) {

                    $semi->increment(
                        'qty',
                        $detail->qty
                    );
                }
            }

            // ================= HAPUS DELIVERY =================
            foreach ($batch->details as $detail) {

                foreach ($detail->processes as $process) {

                    ProcessDelivery::where(
                        'production_batch_detail_process_id',
                        $process->id
                    )->delete();
                }

                // ================= HAPUS PROCESS =================
                $detail->processes()->delete();
            }

            // ================= HAPUS DETAIL =================
            $batch->details()->delete();

            // ================= HAPUS HEADER =================
            $batch->delete();
        });

        return back()->with(
            'success',
            'Produksi Tahap 2 Berhasil Dihapus'
        );
    }

    private function generateWorkflow($request)
    {
        // OBLONG
        if ($request->type == 'oblong') {

            return [

                [
                    'worker_id' => $request->obras_id,
                    'stage' => 'obras',
                ],

                [
                    'worker_id' => $request->penjahit_id,
                    'stage' => 'penjahit',
                ],

                [
                    'worker_id' => $request->obras2_id,
                    'stage' => 'obras2',
                ],

                [
                    'worker_id' => $request->overdek_id,
                    'stage' => 'overdeck_bawah',
                ],
            ];
        }

        // BERKERAH
        if ($request->type == 'berkerah') {

            return [

                [
                    'worker_id' => $request->penjahit_id,
                    'stage' => 'penjahit',
                ],

                [
                    'worker_id' => $request->obras_id,
                    'stage' => 'obras',
                ],

                [
                    'worker_id' => $request->penjahit2_id,
                    'stage' => 'penjahit2',
                ],

                [
                    'worker_id' => $request->overdek_id,
                    'stage' => 'overdeck_bawah',
                ],
            ];
        }

        return [];
    }

    private function calculateBatchTargetQty($process)
    {
        $detail = $process->detail;

        /*
        |--------------------------------------------------------------------------
        | PROSES PERTAMA
        |--------------------------------------------------------------------------
        */

        if ($process->sequence === 1) {

            return $detail->qty;
        }

        /*
        |--------------------------------------------------------------------------
        | PREVIOUS PROCESS
        |--------------------------------------------------------------------------
        */

        $previousProcess =
            ProductionBatchDetailProcess::where(
                'production_batch_detail_id',
                $process->production_batch_detail_id
            )
            ->where(
                'sequence',
                '<',
                $process->sequence
            )
            ->orderByDesc('sequence')
            ->first();

        if (!$previousProcess) {

            return 0;
        }

        return $previousProcess->qty_confirmed ?? 0;
    }

    private function calculateProductionRemainingQty(
        $workerId,
        array $stages
    ) {

        $processes = ProductionBatchDetailProcess::with([
            'detail',
        ])
        ->where('worker_id', $workerId)
        ->whereIn('stage', $stages)
        ->get();

        $totalRemaining = 0;

        $groups = $processes->groupBy(
            'detail.production_batch_id'
        );

        foreach ($groups as $batchId => $items) {

            $target = $items->sum(function ($process) {

                return $this->calculateBatchTargetQty(
                    $process
                );
            });

            $confirmed =
                $items->sum('qty_confirmed');

            if ($confirmed > 0) {

                $completed = $confirmed;

            } else {

                $completed = ProcessProgress::where(
                    'production_batch_id',
                    $batchId
                )
                ->whereHas(
                    'productionBatchProcess',
                    function ($q) use ($items) {

                        $q->where(
                            'stage',
                            $items->first()->stage
                        );
                    }
                )
                ->sum('qty_progress');
            }

            $totalRemaining += max(
                $target - $completed,
                0
            );
        }

        return $totalRemaining;
    }
    private function getWorkerActiveType(
        $workerId
    ) {

        $delivery = ProcessDelivery::with([
            'productionBatchDetailProcess.detail.batch'
        ])
        ->where('worker_id', $workerId)
        ->where('status', 'arrive')
        ->latest('id')
        ->first();

        if (!$delivery) {

            return null;
        }

        return $delivery
            ->productionBatchDetailProcess
            ->detail
            ->batch
            ->type;
    }
    private function getWorkerNextProcess(
        $workerId
    ) {

        $delivery = ProcessDelivery::with([
            'productionBatchDetailProcess'
        ])
        ->where('worker_id', $workerId)
        ->where('status', 'arrive')
        ->latest('id')
        ->first();

        if (!$delivery) {

            return null;
        }

        $currentProcess =
            $delivery
                ->productionBatchDetailProcess;

        $nextProcess =
            ProductionBatchDetailProcess::where(
                'production_batch_detail_id',
                $currentProcess->production_batch_detail_id
            )
            ->where(
                'sequence',
                $currentProcess->sequence + 1
            )
            ->first();

        return $nextProcess
            ? $this->stageLabel(
                $nextProcess->stage
            )
            : 'Selesai';
    }

    private function stageLabel($stage)
    {
        return match ($stage) {

            'obras' => 'Obras 1',

            'penjahit' => 'Penjahit 1',

            'obras2' => 'Obras 2',

            'penjahit2' => 'Penjahit 2',

            'overdeck_bawah' => 'Overdeck Bawah',

            default => $stage,
        };
    }
}
