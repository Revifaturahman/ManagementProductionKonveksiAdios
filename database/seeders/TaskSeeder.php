<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | KONFIGURASI
            |--------------------------------------------------------------------------
            */

            $rawMaterialMasterId = 1;

            // User ID courier
            $courierIds = [2, 3, 4];

            // Worker cutter
            $cutterIds = [1, 2, 3, 4, 5];

            // Worker overdeck tangan
            $overdeckIds = [12, 13];

            $periodStart = '2026-08-12';
            $periodEnd   = '2026-08-19';


            /*
            |--------------------------------------------------------------------------
            | 1. PRODUCTION PERIOD
            |--------------------------------------------------------------------------
            */

            $periodCreatedAt = Carbon::parse(
                '2026-08-12 08:00:00'
            );

            $periodId = DB::table('production_periods')
                ->insertGetId([
                    'code' => 'PRD001',
                    'start_date' => $periodStart,
                    'end_date' => $periodEnd,
                    'status' => 'active',
                    'created_at' => $periodCreatedAt,
                    'updated_at' => $periodCreatedAt,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 2. PRODUCTION PLANNING
            |--------------------------------------------------------------------------
            */

            $planningCreatedAt = Carbon::parse(
                '2026-08-12 08:05:00'
            );

            $planningId = DB::table('production_plannings')
                ->insertGetId([
                    'production_period_id' => $periodId,
                    'raw_material_master_id' => $rawMaterialMasterId,
                    'status' => 'process',
                    'notes' => 'Seeder produksi tahap 1',
                    'created_at' => $planningCreatedAt,
                    'updated_at' => $planningCreatedAt,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 3. PRODUCTION PLANNING ITEMS
            |--------------------------------------------------------------------------
            */

            $planningItems = [
                [
                    'product_variant_id' => 12,
                    'priority_order' => 1,
                    'estimated_kg' => 15,
                    'estimated_qty' => 60,
                ],
                [
                    'product_variant_id' => 4,
                    'priority_order' => 2,
                    'estimated_kg' => 15,
                    'estimated_qty' => 60,
                ],
                [
                    'product_variant_id' => 8,
                    'priority_order' => 3,
                    'estimated_kg' => 17,
                    'estimated_qty' => 51,
                ],
                [
                    'product_variant_id' => 16,
                    'priority_order' => 4,
                    'estimated_kg' => 4,
                    'estimated_qty' => 32,
                ],
                [
                    'product_variant_id' => 20,
                    'priority_order' => 5,
                    'estimated_kg' => 4,
                    'estimated_qty' => 32,
                ],
                [
                    'product_variant_id' => 5,
                    'priority_order' => 6,
                    'estimated_kg' => 16,
                    'estimated_qty' => 48,
                ],
                [
                    'product_variant_id' => 9,
                    'priority_order' => 7,
                    'estimated_kg' => 13,
                    'estimated_qty' => 52,
                ],
                [
                    'product_variant_id' => 1,
                    'priority_order' => 8,
                    'estimated_kg' => 12,
                    'estimated_qty' => 48,
                ],
                [
                    'product_variant_id' => 13,
                    'priority_order' => 9,
                    'estimated_kg' => 4,
                    'estimated_qty' => 32,
                ],
                [
                    'product_variant_id' => 7,
                    'priority_order' => 10,
                    'estimated_kg' => 14,
                    'estimated_qty' => 42,
                ],
                [
                    'product_variant_id' => 11,
                    'priority_order' => 11,
                    'estimated_kg' => 12,
                    'estimated_qty' => 48,
                ],
                [
                    'product_variant_id' => 17,
                    'priority_order' => 12,
                    'estimated_kg' => 4,
                    'estimated_qty' => 32,
                ],
                [
                    'product_variant_id' => 3,
                    'priority_order' => 13,
                    'estimated_kg' => 11,
                    'estimated_qty' => 44,
                ],
                [
                    'product_variant_id' => 15,
                    'priority_order' => 14,
                    'estimated_kg' => 3,
                    'estimated_qty' => 24,
                ],
                [
                    'product_variant_id' => 6,
                    'priority_order' => 15,
                    'estimated_kg' => 13,
                    'estimated_qty' => 39,
                ],
                [
                    'product_variant_id' => 10,
                    'priority_order' => 16,
                    'estimated_kg' => 11,
                    'estimated_qty' => 44,
                ],
                [
                    'product_variant_id' => 14,
                    'priority_order' => 17,
                    'estimated_kg' => 3,
                    'estimated_qty' => 24,
                ],
                [
                    'product_variant_id' => 19,
                    'priority_order' => 18,
                    'estimated_kg' => 3,
                    'estimated_qty' => 24,
                ],
                [
                    'product_variant_id' => 2,
                    'priority_order' => 19,
                    'estimated_kg' => 10,
                    'estimated_qty' => 40,
                ],
                [
                    'product_variant_id' => 18,
                    'priority_order' => 20,
                    'estimated_kg' => 3,
                    'estimated_qty' => 24,
                ],
            ];


            /*
            |--------------------------------------------------------------------------
            | 4. BUAT PLANNING ITEMS
            |--------------------------------------------------------------------------
            */

            $itemIds = [];

            foreach ($planningItems as $item) {

                $itemId = DB::table('production_planning_items')
                    ->insertGetId([
                        'production_planning_id' =>
                            $planningId,

                        'product_variant_id' =>
                            $item['product_variant_id'],

                        'priority_order' =>
                            $item['priority_order'],

                        'estimated_kg' =>
                            $item['estimated_kg'],

                        /*
                         * Sementara seluruh planning masih tersisa.
                         * Akan dikurangi setelah task dibuat.
                         */
                        'remaining_kg' =>
                            $item['estimated_kg'],

                        'estimated_qty' =>
                            $item['estimated_qty'],

                        'created_at' =>
                            $planningCreatedAt,

                        'updated_at' =>
                            $planningCreatedAt,
                    ]);

                $itemIds[$item['priority_order']] = $itemId;
            }


            /*
            |--------------------------------------------------------------------------
            | 5. DATA 3 TASK DALAM 1 SIKLUS
            |--------------------------------------------------------------------------
            |
            | TASK 1
            | 15 + 15 = 30 KG
            |
            | TASK 2
            | 17 + 4 + 4 + 5 = 30 KG
            |
            | TASK 3
            | 11 + 13 + 6 = 30 KG
            |
            */

            $tasks = [

                /*
                |--------------------------------------------------------------------------
                | TASK 1 — FINISHED
                |--------------------------------------------------------------------------
                */

                [
                    'date' => '2026-08-12',
                    'time' => '08:00:00',

                    'courier_id' => 2,

                    'cutter_id' => 1,
                    'overdeck_id' => 12,

                    'status' => 'finished',

                    'items' => [
                        [
                            'priority' => 1,
                            'weight' => 15,
                            'qty' => 60,
                        ],
                        [
                            'priority' => 2,
                            'weight' => 15,
                            'qty' => 60,
                        ],
                    ],
                ],


                /*
                |--------------------------------------------------------------------------
                | TASK 2 — PROCESS
                |--------------------------------------------------------------------------
                |
                | Cutter sudah selesai.
                | Hasil pemotongan sedang dibawa
                | menuju overdeck tangan.
                |
                */

                [
                    'date' => '2026-08-12',
                    'time' => '13:00:00',

                    'courier_id' => 3,

                    'cutter_id' => 1,
                    'overdeck_id' => 13,

                    'status' => 'process',

                    'items' => [
                        [
                            'priority' => 3,
                            'weight' => 17,
                            'qty' => 51,
                        ],
                        [
                            'priority' => 4,
                            'weight' => 4,
                            'qty' => 32,
                        ],
                        [
                            'priority' => 5,
                            'weight' => 4,
                            'qty' => 32,
                        ],
                        [
                            'priority' => 6,
                            'weight' => 5,
                            'qty' => 15,
                        ],
                    ],
                ],


                /*
                |--------------------------------------------------------------------------
                | TASK 3 — PROCESS
                |--------------------------------------------------------------------------
                |
                | Sisa item 6 = 11 KG
                |
                | 11 + 13 + 6 = 30 KG
                |
                | Cutter belum selesai.
                | Kurir sudah datang ke cutter,
                | tetapi belum ada hasil yang siap
                | dibawa ke overdeck.
                |
                */

                [
                    'date' => '2026-08-13',
                    'time' => '08:30:00',

                    'courier_id' => 4,

                    'cutter_id' => 1,
                    'overdeck_id' => 12,

                    'status' => 'process',

                    'items' => [
                        [
                            'priority' => 6,
                            'weight' => 11,
                            'qty' => 33,
                        ],
                        [
                            'priority' => 7,
                            'weight' => 13,
                            'qty' => 52,
                        ],
                        [
                            'priority' => 8,
                            'weight' => 6,
                            'qty' => 24,
                        ],
                    ],
                ],
            ];


            /*
            |--------------------------------------------------------------------------
            | 6. BUAT TASK
            |--------------------------------------------------------------------------
            */

            foreach ($tasks as $task) {

                $startedAt = Carbon::parse(
                    $task['date'] . ' ' . $task['time']
                );


                /*
                |--------------------------------------------------------------------------
                | TOTAL KG TASK
                |--------------------------------------------------------------------------
                */

                $totalWeight = collect($task['items'])
                    ->sum('weight');

                if ($totalWeight > 30) {
                    throw new \Exception(
                        "Task melebihi kapasitas 30 KG."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | RAW MATERIAL
                |--------------------------------------------------------------------------
                */

                $rawMaterialId =
                    DB::table('raw_materials')
                        ->insertGetId([
                            'courier_id' =>
                                $task['courier_id'],

                            'date' =>
                                $task['date'],

                            'status' =>
                                $task['status'],

                            'cycle_started_at' =>
                                $startedAt,

                            'created_at' =>
                                $startedAt,

                            'updated_at' =>
                                $startedAt,
                        ]);


                /*
                |--------------------------------------------------------------------------
                | DETAIL TASK
                |--------------------------------------------------------------------------
                */

                foreach ($task['items'] as $item) {

                    $priority = $item['priority'];

                    $planningItem =
                        $planningItems[$priority - 1];

                    $planningItemId =
                        $itemIds[$priority];


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE REMAINING KG
                    |--------------------------------------------------------------------------
                    */

                    DB::table('production_planning_items')
                        ->where('id', $planningItemId)
                        ->decrement(
                            'remaining_kg',
                            $item['weight']
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | RAW MATERIAL DETAIL
                    |--------------------------------------------------------------------------
                    */

                    $detailId =
                        DB::table('raw_material_details')
                            ->insertGetId([
                                'raw_material_id' =>
                                    $rawMaterialId,

                                'production_planning_item_id' =>
                                    $planningItemId,

                                'product_variant_id' =>
                                    $planningItem[
                                        'product_variant_id'
                                    ],

                                'weight' =>
                                    $item['weight'],

                                'qty_result' =>
                                    $task['status'] === 'finished'
                                        ? $item['qty']
                                        : null,

                                'created_at' =>
                                    $startedAt,

                                'updated_at' =>
                                    $startedAt,
                            ]);


                    /*
                    |--------------------------------------------------------------------------
                    | CUTTER PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $cutterProcessId =
                        DB::table(
                            'raw_material_detail_processes'
                        )->insertGetId([
                            'raw_material_detail_id' =>
                                $detailId,

                            'worker_id' =>
                                $task['cutter_id'],

                            'stage' =>
                                'cutter',

                            'sequence' =>
                                1,

                            /*
                            | Task 1 dan Task 2:
                            | cutter sudah selesai.
                            |
                            | Task 3:
                            | cutter belum selesai.
                            */
                            'qty_confirmed' =>
                                in_array(
                                    $task['status'],
                                    ['finished']
                                ) ||
                                $task['date'] === '2026-08-12'
                                    && $task['time'] === '13:00:00'
                                    ? $item['qty']
                                    : 0,

                            'created_at' =>
                                $startedAt,

                            'updated_at' =>
                                $startedAt,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | OVERDECK PROCESS
                    |--------------------------------------------------------------------------
                    */

                    $overdeckProcessId =
                        DB::table(
                            'raw_material_detail_processes'
                        )->insertGetId([
                            'raw_material_detail_id' =>
                                $detailId,

                            'worker_id' =>
                                $task['overdeck_id'],

                            'stage' =>
                                'overdeck_tangan',

                            'sequence' =>
                                2,

                            /*
                            | Task 1 sudah selesai.
                            | Task 2 dan 3 belum dikerjakan.
                            */
                            'qty_confirmed' =>
                                $task['status'] === 'finished'
                                    ? $item['qty']
                                    : 0,

                            'created_at' =>
                                $startedAt,

                            'updated_at' =>
                                $startedAt,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY 1
                    | FACTORY → CUTTER
                    |--------------------------------------------------------------------------
                    */

                    $delivery1Started =
                        $startedAt->copy();

                    $delivery1Arrived =
                        $delivery1Started
                            ->copy()
                            ->addMinutes(60);


                    /*
                    | Task 1 dan Task 2:
                    | barang sudah sampai dan diterima cutter.
                    |
                    | Task 3:
                    | barang baru sampai ke cutter.
                    */

                    $cutterFinished =
                        $task['status'] === 'finished'
                        ||
                        (
                            $task['date'] === '2026-08-12'
                            &&
                            $task['time'] === '13:00:00'
                        );


                    if ($cutterFinished) {

                        $delivery1Finished =
                            $delivery1Arrived
                                ->copy()
                                ->addMinutes(5);

                        $delivery1Status =
                            'finished';

                    } else {

                        $delivery1Finished = null;

                        $delivery1Status =
                            'arrive';
                    }


                    DB::table('process_deliveries')
                        ->insert([
                            'raw_material_detail_process_id' =>
                                $cutterProcessId,

                            'production_batch_detail_process_id' =>
                                null,

                            'worker_id' =>
                                $task['cutter_id'],

                            'courier_id' =>
                                $task['courier_id'],

                            'delivered_qty' =>
                                $item['weight'],

                            'delivered_unit' =>
                                'kg',

                            'received_qty' =>
                                $cutterFinished
                                    ? $item['qty']
                                    : null,

                            'received_unit' =>
                                $cutterFinished
                                    ? 'pcs'
                                    : null,

                            'status' =>
                                $delivery1Status,

                            'type' =>
                                'process',

                            'destination_type' =>
                                'worker',

                            'started_at' =>
                                $delivery1Started,

                            'arrived_at' =>
                                $delivery1Arrived,

                            'finished_at' =>
                                $delivery1Finished,

                            'created_at' =>
                                $delivery1Started,

                            'updated_at' =>
                                $delivery1Finished
                                    ?? $delivery1Arrived,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | TASK 3 — KURIR RETURNING
                    |--------------------------------------------------------------------------
                    |
                    | Karena cutter Task 3 belum menghasilkan
                    | pekerjaan yang selesai, kurir kembali
                    | ke konveksi.
                    |
                    */

                    if (
                        $task['date'] === '2026-08-13'
                    ) {

                        $returnStarted =
                            $delivery1Arrived
                                ->copy()
                                ->addMinutes(5);

                        $returnArrived =
                            $returnStarted
                                ->copy()
                                ->addMinutes(60);


                        DB::table('process_deliveries')
                            ->insert([
                                'raw_material_detail_process_id' =>
                                    $cutterProcessId,

                                'production_batch_detail_process_id' =>
                                    null,

                                'worker_id' =>
                                    $task['cutter_id'],

                                'courier_id' =>
                                    $task['courier_id'],

                                'delivered_qty' =>
                                    null,

                                'delivered_unit' =>
                                    null,

                                'received_qty' =>
                                    null,

                                'received_unit' =>
                                    null,

                                /*
                                | KURIR SEDANG KEMBALI
                                */
                                'status' =>
                                    'returning',

                                'type' =>
                                    'return_factory',

                                'destination_type' =>
                                    'factory',

                                'started_at' =>
                                    $returnStarted,

                                'arrived_at' =>
                                    null,

                                'finished_at' =>
                                    null,

                                'created_at' =>
                                    $returnStarted,

                                'updated_at' =>
                                    $returnStarted,
                            ]);

                        /*
                        | Tidak membuat delivery
                        | Factory → Overdeck.
                        |
                        | Karena belum ada hasil cutter
                        | yang selesai untuk dibawa.
                        */

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY 2
                    | CUTTER → FACTORY
                    |--------------------------------------------------------------------------
                    |
                    | Untuk task yang cutter-nya sudah selesai.
                    |
                    */

                    $delivery2Started =
                        $delivery1Finished->copy();

                    $delivery2Arrived =
                        $delivery2Started
                            ->copy()
                            ->addMinutes(60);

                    $delivery2Finished =
                        $delivery2Arrived
                            ->copy()
                            ->addMinutes(5);


                    DB::table('process_deliveries')
                        ->insert([
                            'raw_material_detail_process_id' =>
                                $cutterProcessId,

                            'production_batch_detail_process_id' =>
                                null,

                            'worker_id' =>
                                $task['cutter_id'],

                            'courier_id' =>
                                $task['courier_id'],

                            'delivered_qty' =>
                                $item['qty'],

                            'delivered_unit' =>
                                'pcs',

                            'received_qty' =>
                                null,

                            'received_unit' =>
                                null,

                            'status' =>
                                'finished',

                            'type' =>
                                'return_factory',

                            'destination_type' =>
                                'factory',

                            'started_at' =>
                                $delivery2Started,

                            'arrived_at' =>
                                $delivery2Arrived,

                            'finished_at' =>
                                $delivery2Finished,

                            'created_at' =>
                                $delivery2Started,

                            'updated_at' =>
                                $delivery2Finished,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | TASK 1
                    | FACTORY → OVERDECK
                    |--------------------------------------------------------------------------
                    */

                    if ($task['status'] === 'finished') {

                        $delivery3Started =
                            $delivery2Finished->copy();

                        $delivery3Arrived =
                            $delivery3Started
                                ->copy()
                                ->addMinutes(60);

                        $delivery3Finished =
                            $delivery3Arrived
                                ->copy()
                                ->addMinutes(5);


                        DB::table('process_deliveries')
                            ->insert([
                                'raw_material_detail_process_id' =>
                                    $overdeckProcessId,

                                'production_batch_detail_process_id' =>
                                    null,

                                'worker_id' =>
                                    $task['overdeck_id'],

                                'courier_id' =>
                                    $task['courier_id'],

                                'delivered_qty' =>
                                    $item['qty'],

                                'delivered_unit' =>
                                    'pcs',

                                'received_qty' =>
                                    $item['qty'],

                                'received_unit' =>
                                    'pcs',

                                'status' =>
                                    'finished',

                                'type' =>
                                    'process',

                                'destination_type' =>
                                    'worker',

                                'started_at' =>
                                    $delivery3Started,

                                'arrived_at' =>
                                    $delivery3Arrived,

                                'finished_at' =>
                                    $delivery3Finished,

                                'created_at' =>
                                    $delivery3Started,

                                'updated_at' =>
                                    $delivery3Finished,
                            ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TASK 2
                    | HASIL CUTTER → OVERDECK
                    |--------------------------------------------------------------------------
                    |
                    | Barang sudah selesai dipotong dan sedang
                    | dikirim ke overdeck.
                    |
                    */

                    if (
                        $task['date'] === '2026-08-12'
                        &&
                        $task['time'] === '13:00:00'
                    ) {

                        $delivery3Started =
                            $delivery2Finished->copy();

                        $delivery3Arrived =
                            $delivery3Started
                                ->copy()
                                ->addMinutes(60);


                        DB::table('process_deliveries')
                            ->insert([
                                'raw_material_detail_process_id' =>
                                    $overdeckProcessId,

                                'production_batch_detail_process_id' =>
                                    null,

                                'worker_id' =>
                                    $task['overdeck_id'],

                                'courier_id' =>
                                    $task['courier_id'],

                                'delivered_qty' =>
                                    $item['qty'],

                                'delivered_unit' =>
                                    'pcs',

                                'received_qty' =>
                                    null,

                                'received_unit' =>
                                    null,

                                'status' =>
                                    'arrive',

                                'type' =>
                                    'process',

                                'destination_type' =>
                                    'worker',

                                'started_at' =>
                                    $delivery3Started,

                                'arrived_at' =>
                                    $delivery3Arrived,

                                'finished_at' =>
                                    null,

                                'created_at' =>
                                    $delivery3Started,

                                'updated_at' =>
                                    $delivery3Arrived,
                            ]);
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 7. UPDATE PLANNING
            |--------------------------------------------------------------------------
            */

            DB::table('production_plannings')
                ->where('id', $planningId)
                ->update([
                    'status' => 'process',

                    'updated_at' =>
                        Carbon::parse(
                            '2026-08-13 12:00:00'
                        ),
                ]);
        });
    }
}
