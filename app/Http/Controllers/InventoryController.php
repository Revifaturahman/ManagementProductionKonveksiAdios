<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use App\Models\ProductVariant;
use App\Models\SemiProduct;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $variants = ProductVariant::with([

            'product',

            'semiProduct',

            'finishedProduct',

        ])->get();

        $totalSemi = $variants->sum(function ($variant) {

            return $variant->semiProduct->qty ?? 0;

        });

        $totalFinished = $variants->sum(function ($variant) {

            return $variant->finishedProduct->qty ?? 0;

        });

        $lowStockCount = $variants->filter(function ($variant) {

            $semi =
                $variant->semiProduct->qty ?? 0;

            $finished =
                $variant->finishedProduct->qty ?? 0;

            $total =
                $semi + $finished;

            return $total <= $variant->minimum_stock;

        })->count();

        $lastSemiOpname =
            SemiProduct::max(
                'stock_opname_at'
            );

        $lastFinishedOpname =
            FinishedProduct::max(
                'stock_opname_at'
            );

        $lastOpname =
            collect([
                $lastSemiOpname,
                $lastFinishedOpname
            ])
            ->filter()
            ->max();

        return view(
            'productionPlanning.inventoryProduksi',
            compact(
                'variants',
                'totalSemi',
                'totalFinished',
                'lowStockCount',
                'lastOpname'
            )
        );
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
        //
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
    public function update(Request $request,ProductVariant $production_inventory)
    {
        $request->validate([
            'semi_qty' => [
                'required',
                'integer',
                'min:0'
            ],

            'finished_qty' => [
                'required',
                'integer',
                'min:0'
            ],

            'minimum_stock' => [
                'required',
                'integer',
                'min:0'
            ],

        ], [

            'semi_qty.min' =>
                'Jumlah produk setengah jadi tidak boleh kurang dari 0.',

            'finished_qty.min' =>
                'Jumlah produk jadi tidak boleh kurang dari 0.',

            'minimum_stock.min' =>
                'Stok minimum tidak boleh kurang dari 0.',

        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE SEMI PRODUCT
        |--------------------------------------------------------------------------
        */

        $production_inventory
            ->semiProduct()
            ->updateOrCreate(
                [
                    'product_variant_id' =>
                        $production_inventory->id
                ],
                [
                    'qty' =>
                        $request->semi_qty,
                    
                    'stock_opname_at' =>
                        now(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | UPDATE FINISHED PRODUCT
        |--------------------------------------------------------------------------
        */

        $production_inventory
            ->finishedProduct()
            ->updateOrCreate(
                [
                    'product_variant_id' =>
                        $production_inventory->id
                ],
                [
                    'qty' =>
                        $request->finished_qty,

                    'stock_opname_at' =>
                        now(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | UPDATE MINIMUM STOCK
        |--------------------------------------------------------------------------
        */

        $production_inventory->update([

            'minimum_stock' =>
                $request->minimum_stock

        ]);

        return redirect()

            ->route('production_inventory.index')

            ->with(
                'success',
                'Persediaan berhasil diperbarui'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'variants' => ['required', 'array'],

            'variants.*.semi_qty' => [
                'required',
                'integer',
                'min:0'
            ],

            'variants.*.finished_qty' => [
                'required',
                'integer',
                'min:0'
            ],

            'variants.*.minimum_stock' => [
                'required',
                'integer',
                'min:0'
            ],

        ], [

            'variants.*.semi_qty.min' =>
                'Jumlah produk setengah jadi tidak boleh kurang dari 0.',

            'variants.*.finished_qty.min' =>
                'Jumlah produk jadi tidak boleh kurang dari 0.',

            'variants.*.minimum_stock.min' =>
                'Stok minimum tidak boleh kurang dari 0.',

        ]);

        foreach ($request->variants as $variantId => $data) {

            $variant = ProductVariant::with([
                'semiProduct',
                'finishedProduct'
            ])->findOrFail($variantId);

            /*
            |--------------------------------------------------------------------------
            | UPDATE SEMI PRODUCT
            |--------------------------------------------------------------------------
            */

            $variant
                ->semiProduct()
                ->updateOrCreate(
                    [
                        'product_variant_id' => $variant->id
                    ],
                    [
                        'qty' => $data['semi_qty']
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | UPDATE FINISHED PRODUCT
            |--------------------------------------------------------------------------
            */

            $variant
                ->finishedProduct()
                ->updateOrCreate(
                    [
                        'product_variant_id' => $variant->id
                    ],
                    [
                        'qty' => $data['finished_qty']
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | UPDATE MINIMUM STOCK
            |--------------------------------------------------------------------------
            */

            $variant->update([
                'minimum_stock' => $data['minimum_stock']
            ]);
        }

        return redirect()
            ->route('production_inventory.index')
            ->with(
                'success',
                'Persediaan berhasil diperbarui.'
            );
    }
}
