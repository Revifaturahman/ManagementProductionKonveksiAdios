<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthControllerWebsite extends Controller
{
    public function showLogin()
    {
        return view('login.index');
    }
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        $user = User::where(
            'username',
            $request->username
        )->first();

        if (
            !$user ||
            !Hash::check($request->password, $user->password)
        ) {
            return back()
                ->withErrors([
                    'login' => 'Username atau password salah.',
                ])
                ->withInput();
        }

        if ($user->role !== 'admin') {
            return back()
                ->withErrors([
                    'login' => 'Akun tidak memiliki akses admin.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN SESSION
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgotPassword()
    {
        return view('login.forgotPassword');
    }

    public function forgotPasswordPost(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'phone' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'forgot' => 'Username tidak ditemukan.'
                ])
                ->withInput();
        }

        // Cek nomor telepon
        if ($user->phone != $request->phone) {
            return back()
                ->withErrors([
                    'forgot' => 'Jawaban pertanyaan tidak sesuai.'
                ])
                ->withInput();
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()
            ->route('login')
            ->with('success', 'Password berhasil diperbarui. Silakan login kembali.');
    }
}
