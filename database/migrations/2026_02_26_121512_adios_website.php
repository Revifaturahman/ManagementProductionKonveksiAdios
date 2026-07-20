<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | WORKERS
        |--------------------------------------------------------------------------
        */
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            // $table->text('address')->nullable();
            $table->enum('role', [
                'cutter',
                'tailor',
                'overdeck',
                'obras'
            ]);
            $table->enum('overdeck_type', [
                'tangan',
                'bawah'
            ])->nullable();
            $table->decimal('rate_per_piece', 10, 2)->default(0);
            $table->text('address')->nullable();
            $table->decimal('latitude',10,7)->nullable();
            $table->decimal('longitude',10,7)->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('product_categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->integer('ratio_per_kg');
            $table->integer('allocation_ratio')
          ->default(1);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('size');
            $table->integer('minimum_stock')
                ->default(0);
            $table->timestamps();
        });

        Schema::create('raw_material_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('raw_material_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_master_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('stock_kg', 10, 2)
                ->default(0);
            $table->timestamps();
            $table->unique('raw_material_master_id');
        });

        Schema::create('raw_material_stock_movements', function (Blueprint $table)
        {
            $table->id();

            $table->foreignId(
                'raw_material_master_id'
            )->constrained()
            ->cascadeOnDelete();

            $table->enum('type', [
                'in',
                'out'
            ]);

            $table->decimal(
                'qty_kg',
                10,
                2
            );

            $table->date('transaction_date');

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('production_periods', function (Blueprint $table) {

            $table->id();

            $table->string('code')->unique();

            $table->date('start_date');

            $table->date('end_date');

            $table->enum('status', [
                'pending',
                'active',
                'finished'
            ])->default('active');

            $table->timestamps();
        });

        Schema::create('production_plannings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('production_period_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('raw_material_master_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'process',
                'finished'
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('production_planning_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('production_planning_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('priority_order');

            $table->decimal('estimated_kg', 8, 2);

            $table->decimal('remaining_kg', 8, 2);

            $table->decimal('estimated_qty', 8, 2);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | RAW MATERIAL (PRODUCTION STAGE 1)
        |--------------------------------------------------------------------------
        */
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('courier_id')
                ->constrained('users');

            $table->date('date');

            $table->enum('status', [
                'pending',
                'process',
                'finished'
            ])->default('pending');

            $table->timestamp('cycle_started_at')->nullable();

            $table->timestamps();
        });

        Schema::create('raw_material_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raw_material_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('production_planning_item_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // product + size
            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            // bahan sebelum cutting
            $table->decimal('weight', 8, 2);

            // hasil cutting aktual
            $table->integer('qty_result')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('raw_material_detail_processes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raw_material_detail_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('worker_id')
            ->constrained('workers')
            ->cascadeOnDelete();

            $table->enum('stage', [
                'cutter',
                'overdeck_tangan',
            ]);

            // urutan proses
            $table->integer('sequence');

            // total hasil final process ini
            $table->integer('qty_confirmed')
                ->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | SEMI PRODUCTS STOCK
        |--------------------------------------------------------------------------
        */
        Schema::create('semi_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('qty')->default(0);
            $table->timestamps();
            $table->unique('product_variant_id');
            $table->timestamp(
                'stock_opname_at'
            )->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION STAGE 2
        |--------------------------------------------------------------------------
        */
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('courier_id')
                ->constrained('users');

            $table->enum('type', [
                'oblong',
                'berkerah'
            ]);

            $table->date('date');

            $table->enum('status', [
                'pending',
                'process',
                'finished',
            ])->default('pending');

            $table->timestamp('cycle_started_at')->nullable();

            $table->timestamps();
        });

        Schema::create('production_batch_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('production_planning_item_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
                
            $table->foreignId('product_variant_id')
                ->constrained();
            $table->integer('qty');
            $table->timestamps();
        });

        Schema::create('production_batch_detail_processes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_batch_detail_id');

            $table->foreign(
                'production_batch_detail_id',
                'pbdp_fk'
            )->references('id')
            ->on('production_batch_details')
            ->cascadeOnDelete();

            $table->foreignId('worker_id')
            ->constrained('workers')
            ->cascadeOnDelete();

            $table->enum('stage', [
                'obras',
                'penjahit',
                'obras2',
                'penjahit2',
                'overdeck_bawah',
            ]);

            $table->integer('sequence');

            $table->integer('qty_confirmed')
                ->default(0);

            $table->timestamps();
        });
        /*
        |--------------------------------------------------------------------------
        | FINISHED PRODUCT STOCK
        |--------------------------------------------------------------------------
        */
        Schema::create('finished_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('qty')->default(0);
            $table->timestamps();
            $table->unique('product_variant_id');
            $table->timestamp(
                'stock_opname_at'
            )->nullable();
        });
        /*
        |--------------------------------------------------------------------------
        | PROCESS PROGRESS MAKLUN
        |--------------------------------------------------------------------------
        */
        Schema::create('process_progresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raw_material_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('production_batch_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('raw_material_detail_process_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('production_batch_detail_process_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            // laporan harian maklun
            $table->date('progress_date');

            // hasil kerja hari ini
            $table->integer('qty_progress');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
        
        Schema::create('process_deliveries', function (Blueprint $table) {
            $table->id();

            // $table->foreignId('raw_material_id')
            //     ->nullable()
            //     ->constrained()
            //     ->cascadeOnDelete();

            // $table->foreignId('production_batch_id')
            //     ->nullable()
            //     ->constrained()
            //     ->cascadeOnDelete();

            // nullable salah satu
            $table->foreignId('raw_material_detail_process_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('production_batch_detail_process_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            // worker tujuan kunjungan
            $table->foreignId('worker_id')
            ->constrained('workers')
            ->cascadeOnDelete();

            $table->foreignId('courier_id')
                ->constrained('users');

            /*
            barang DIANTAR
            */
            $table->decimal('delivered_qty', 10, 2)
                ->nullable();

            $table->enum('delivered_unit', [
                'kg',
                'pcs'
            ])->nullable();

            $table->decimal(
                'received_qty',
                10,
                2
            )->nullable();

            $table->string(
                'received_unit'
            )->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'arrive',
                'finished'
            ])->default('pending');

            $table->enum('type', [

                'process',

                'return_factory',
            ])->default('process');

            $table->enum('destination_type', [

                'worker',

                'factory',
            ])->default('worker');

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('arrived_at')
                ->nullable();

            $table->timestamp('finished_at')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('company_profiles', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->text('address');

            $table->string('latitude');

            $table->string('longitude');

            $table->timestamps();
        });
        
    }

    /*
    |--------------------------------------------------------------------------
    | DOWN
    |--------------------------------------------------------------------------
    */
    public function down(): void
    {
        Schema::dropIfExists('production_periods');
        Schema::dropIfExists('production_plannings');
        Schema::dropIfExists('process_deliveries');
        Schema::dropIfExists('process_progresses');
        Schema::dropIfExists('finished_products');
        Schema::dropIfExists('production_batch_details');
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('semi_products');
        Schema::dropIfExists('raw_material_details');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('workers');
    }
};