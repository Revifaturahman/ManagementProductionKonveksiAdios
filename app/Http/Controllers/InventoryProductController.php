<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use App\Models\SemiProduct;
use Illuminate\Http\Request;

class InventoryProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventory = [];

        $semiProducts =
            SemiProduct::with(
                'variant.product'
            )->get();

        foreach ($semiProducts as $item) {

            $productId =
                $item->variant->product->id;

            if (!isset($inventory[$productId])) {

                $inventory[$productId] = [

                    'product' =>
                        $item->variant->product,

                    'total_qty' => 0,

                    'semi_products' =>
                        collect(),

                    'finished_products' =>
                        collect(),
                ];
            }

            $inventory[$productId]['total_qty']
                += $item->qty;

            $inventory[$productId]['semi_products']
                ->push($item);
        }

        $finishedProducts =
            FinishedProduct::with(
                'variant.product'
            )->get();

        foreach ($finishedProducts as $item) {

            $productId =
                $item->variant->product->id;

            if (!isset($inventory[$productId])) {

                $inventory[$productId] = [

                    'product' =>
                        $item->variant->product,

                    'total_qty' => 0,

                    'semi_products' =>
                        collect(),

                    'finished_products' =>
                        collect(),
                ];
            }

            $inventory[$productId]['total_qty']
                += $item->qty;

            $inventory[$productId]['finished_products']
                ->push($item);
        }

        /*
        |--------------------------------------------------------------------------
        | LAST STOCK OPNAME
        |--------------------------------------------------------------------------
        */

        $lastSemiUpdate =
            SemiProduct::max('updated_at');

        $lastFinishedUpdate =
            FinishedProduct::max('updated_at');

        $lastOpname =
            collect([
                $lastSemiUpdate,
                $lastFinishedUpdate
            ])->filter()->max();

        return view(
            'inventory.inventory_product',
            [

                'inventory' =>
                    collect($inventory),

                'lastOpname' =>
                    $lastOpname,
            ]
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
