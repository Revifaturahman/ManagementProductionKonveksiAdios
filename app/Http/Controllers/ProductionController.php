<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Worker::with('user');

        if ($request->role) {
            $query->where('role', $request->role);
        }

        return view('worker.production', [
            'productions' => $query->get(),
            'selectedRole' => $request->role
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
            'phone' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'overdeck_type' => 'nullable|string|max:255',
            'rate_per_piece' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {

            if ($request->id) {

                $worker = Worker::findOrFail($request->id);

                $worker->fill($validated);

                // Tidak ada perubahan data
                if (!$worker->isDirty()) {

                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Data maklun gagal diubah karena tidak terdapat perubahan data.');
                }

                $worker->save();

                return redirect()->back()
                    ->with('success', 'Data maklun berhasil diperbarui.');
            } else {

                if (Worker::where('name', $validated['name'])->exists()) {

                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Data maklun gagal ditambahkan karena nama sudah terdaftar.');
                }

                Worker::create($validated);

                return redirect()->back()
                    ->with('success', 'Data maklun berhasil ditambahkan.');
            }

        } catch (\Exception $e) {

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data maklun.');
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
    public function destroy(Worker $worker)
    {
        $worker->delete();
        return redirect()->back()->with('success', 'Data pekerja berhasil dihapus.');
    }
}
