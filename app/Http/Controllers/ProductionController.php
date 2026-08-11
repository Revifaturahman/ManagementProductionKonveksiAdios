<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        /*
        |--------------------------------------------------------------------------
        | EDIT
        |--------------------------------------------------------------------------
        */

        if ($request->id) {

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

                $worker = Worker::findOrFail($request->id);

                $worker->fill([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'role' => $validated['role'],
                    'overdeck_type' => $validated['overdeck_type'] ?? null,
                    'rate_per_piece' => $validated['rate_per_piece'],
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);

                if (!$worker->isDirty()) {

                    return redirect()
                        ->route('workers.index')
                        ->with(
                            'error',
                            'Data maklun gagal diubah karena tidak terdapat perubahan data.'
                        );
                }

                $worker->save();

                return redirect()
                    ->route('workers.index')
                    ->with(
                        'success',
                        'Data maklun berhasil diubah.'
                    );

            } catch (\Exception $e) {

                return redirect()
                    ->route('workers.index')
                    ->with(
                        'error',
                        'Terjadi kesalahan saat memperbarui data maklun.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            // Akun
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',

            // Worker
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'overdeck_type' => 'nullable|string|max:255',
            'rate_per_piece' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        try {

            $exists = Worker::where('name', $validated['name'])
                ->exists();

            if ($exists) {

                return redirect()
                    ->route('workers.index')
                    ->withInput()
                    ->with(
                        'error',
                        'Data maklun gagal ditambahkan karena nama sudah terdaftar.'
                    );
            }

            DB::transaction(function () use ($validated) {

                /*
                |--------------------------------------------------------------------------
                | CREATE WORKER
                |--------------------------------------------------------------------------
                */

                $worker = Worker::create([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'role' => $validated['role'],
                    'overdeck_type' => $validated['overdeck_type'] ?? null,
                    'rate_per_piece' => $validated['rate_per_piece'],
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);


                /*
                |--------------------------------------------------------------------------
                | CREATE USER
                |--------------------------------------------------------------------------
                */

                User::create([
                    'name' => $worker->name,
                    'username' => $validated['username'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $worker->phone,
                    'role' => 'production',
                    'worker_id' => $worker->id,
                ]);
            });

            return redirect()
                ->route('workers.index')
                ->with(
                    'success',
                    'Data maklun dan akun berhasil ditambahkan.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->route('workers.index')
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan saat menambahkan data maklun.'
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
    public function destroy(Worker $worker)
    {
        $worker->delete();
        return redirect()->back()->with('success', 'Data pekerja berhasil dihapus.');
    }
}
