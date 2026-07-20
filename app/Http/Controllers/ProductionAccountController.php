<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProductionAccountController extends Controller
{
    public function index()
    {
        return view('worker.production');
    }

    public function create(){
        $validated = request()->validate([
            'account_production_id' => 'required|exists:workers,id',
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8',
            'account_phone' => 'required|string|max:20',
        ], [
            'username.unique' => 'Username sudah digunakan.',
        ]);
        User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'password' => bcrypt($validated['password']),
                'phone' => $validated['account_phone'],
                'role' => 'production',
                'worker_id' => $validated['account_production_id'],
            ]);
        return redirect()->back()
        ->with('success', 'Akun produksi berhasil dibuat');
    }

    public function delete($id){
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()
            ->with('success', 'Akun produksi berhasil dihapus');
    }
}
