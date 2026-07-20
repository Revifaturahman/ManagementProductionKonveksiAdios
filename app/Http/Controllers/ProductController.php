<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('product.product', [
            'products' => Product::with('category')->get(),
            'categories' => ProductCategory::all(),
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|exists:product_categories,id',
        ]);

        if($request->id) {
            try {
                $product = Product::findOrFail($request->id);

                $product->fill([
                    'name' => $validated['name'],
                    'category_id' => $validated['category'],
                ]);

                if (!$product->isDirty()) {

                    return redirect()
                        ->route('product.index')
                        ->with(
                            'error',
                            'Produk gagal diubah karena tidak terdapat perubahan data.'
                        );
                }

                $product->save();

                return redirect()
                    ->route('product.index')
                    ->with(
                        'success',
                        'Produk berhasil diubah.'
                    );
            } catch (\Exception $e) {
                return redirect()->route('product.index')->with('error', 'Terjadi kesalahan saat memperbarui produk.');
            }
        } else {
            try {
                $exists = Product::where('name', $validated['name'])
                    ->where('category_id', $validated['category'])
                    ->exists();

                if ($exists) {

                    return redirect()
                        ->route('product.index')
                        ->withInput()
                        ->with(
                            'error',
                            'Produk gagal ditambahkan karena nama produk pada kategori tersebut sudah terdaftar.'
                        );
                }
                Product::create([
                    'name' => $validated['name'],
                    'category_id' => $validated['category'],
                ]);
                return redirect()->route('product.index')->with('success', 'Produk berhasil ditambahkan.');
            } catch (\Exception $e) {
                return redirect()->route('product.index')->with('error', 'Terjadi kesalahan saat menambahkan produk.');
            }
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
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Produk berhasil dihapus.');
    }
}
