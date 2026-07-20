<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([

            'username' => 'required',

            'password' => 'required',

        ]);

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        $user = User::with('worker')

            ->where(
                'username',
                $request->username
            )

            ->first();

        if (!$user) {

            return response()->json([

                'success' => false,

                'message' => 'Username tidak ditemukan',

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | PASSWORD
        |--------------------------------------------------------------------------
        */

        if (!Hash::check(
            $request->password,
            $user->password
        )) {

            return response()->json([

                'success' => false,

                'message' => 'Password salah',

            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | SANCTUM TOKEN
        |--------------------------------------------------------------------------
        */

        $user->tokens()->delete();

        $token = $user
            ->createToken('mobile-token')
            ->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' => 'Login berhasil',

            'token' => $token,

            'data' => [

                'id' => $user->id,

                'name' => $user->name,

                'username' => $user->username,

                'phone' => $user->phone,

                'role' => $user->role,

                'worker_id' => $user->worker_id,

                'worker_role' =>
                    $user->worker->role ?? null,

                'is_active' => (bool) $user->is_active,

            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([

            'old_password' => 'required',

            'new_password' => 'required|min:8',

            'new_password_confirmation' =>
                'required|same:new_password',

        ]);

        /*
        |--------------------------------------------------------------------------
        | USER LOGIN
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | CEK PASSWORD LAMA
        |--------------------------------------------------------------------------
        */

        if (!Hash::check(
            $request->old_password,
            $user->password
        )) {

            return response()->json([

                'success' => false,

                'message' => 'Password lama tidak sesuai',

            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PASSWORD
        |--------------------------------------------------------------------------
        */

        $user->update([

            'password' => Hash::make(
                $request->new_password
            ),

        ]);

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' => 'Password berhasil diperbarui',

        ]);
    }

    public function updateAvailability(Request $request)
    {
        $request->validate([
            'is_active' => ['required', 'boolean']
        ]);

        $user = $request->user();

        $user->update([
            'is_active' => $request->boolean('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui',
            'data' => [
                'is_active' => $user->is_active
            ]
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'phone' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan.'
            ], 404);
        }

        if ($user->phone != $request->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor telepon tidak sesuai.'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.'
        ]);
    }
}