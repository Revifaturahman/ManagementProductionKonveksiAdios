<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('worker.courier', [
            'couriers' => User::where('role', 'courier')->get(),
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
        if ($request->id) {

            $validated = $request->validate([
                'name'  => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);

            try {

                $courier = User::findOrFail($request->id);

                $courier->name  = $validated['name'];
                $courier->phone = $validated['phone'];

                // Tidak ada perubahan
                if (!$courier->isDirty()) {
                    return redirect()->back()
                        ->with('error', 'Data pekerja kurir gagal diubah karena tidak terdapat perubahan data.');
                }

                $courier->save();

                return redirect()->back()
                    ->with('success', 'Kurir berhasil diperbarui');

            } catch (\Exception $e) {

                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan saat memperbarui kurir');
            }

        } else {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:8',
                'phone' => 'required|string|max:20',
            ], [
                'username.unique' => 'Username sudah digunakan.',
            ]);

            try {

                User::create([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'password' => bcrypt($validated['password']),
                    'phone' => $validated['phone'],
                    'role' => 'courier',
                ]);

                return redirect()->back()
                    ->with('success', 'Kurir berhasil ditambahkan');

            } catch (\Exception $e) {

                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan saat menambahkan kurir');
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
    public function destroy(User $courier)
    {
        User::find($courier->id)->delete();
        return redirect()->back()->with('success', 'Kurir berhasil dihapus');
    }
}
