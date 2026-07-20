<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SemiProduct;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('product.variant', [
            'variants' => ProductVariant::with('product')->get(),
            'products' => Product::all(),
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
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'required|string|max:255',
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            if ($request->filled('variant_id')) {

                $variant = ProductVariant::findOrFail($request->variant_id);

                $variant->fill([
                    'product_id' => $validated['product_id'],
                    'size' => $validated['size'],
                ]);

                if (!$variant->isDirty()) {

                    return redirect()
                        ->route('variant.index')
                        ->with(
                            'error',
                            'Varian gagal diubah karena tidak terdapat perubahan data.'
                        );
                }

                $variant->save();

                // semi product
                $variant->semiProduct()->updateOrCreate(
                    [],
                    [
                        'qty' => 0,
                    ]
                );

                // finished product
                $variant->finishedProduct()->updateOrCreate(
                    [],
                    [
                        'qty' => 0,
                    ]
                );

                return redirect()
                    ->route('variant.index')
                    ->with(
                        'success',
                        'Varian berhasil diperbarui.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | CHECK DUPLICATE
            |--------------------------------------------------------------------------
            */

            $exists = ProductVariant::where(
                    'product_id',
                    $validated['product_id']
                )
                ->where(
                    'size',
                    $validated['size']
                )
                ->exists();

            if ($exists) {

                return redirect()
                    ->route('variant.index')
                    ->withInput()
                    ->with(
                        'error',
                        'Varian gagal ditambahkan karena ukuran produk tersebut sudah terdaftar.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE
            |--------------------------------------------------------------------------
            */

            $variant = ProductVariant::create([
                'product_id' => $validated['product_id'],
                'size' => $validated['size'],
            ]);

            // semi product
            $variant->semiProduct()->create([
                'qty' => 0,
            ]);

            // finished product
            $variant->finishedProduct()->create([
                'qty' => 0,
            ]);

            return redirect()
                ->route('variant.index')
                ->with(
                    'success',
                    'Varian berhasil ditambahkan.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->route('variant.index')
                ->with(
                    'error',
                    'Terjadi kesalahan saat menyimpan varian.'
                );
        }
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
    public function destroy(ProductVariant $variant)
    {
        try {
            $variant->delete();
            return redirect()->route('variant.index')->with('success', 'Varian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('variant.index')->with('error', 'Terjadi kesalahan saat menghapus varian.');
        }
    }
}
