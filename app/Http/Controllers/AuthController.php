<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\JWTGuard;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    public function register(Request $request) {
        $data = $request->validate([
            'name'=>'required|string',
            'email'=>'required|email|unique:users',
            'password'=>'required|string|confirmed',
            'role'=>'sometimes|in:admin,staff,customer',
        ]);

        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'role'=>$data['role'] ?? 'customer'
        ]);

        /** @var JWTSubject $jwtUser */
        $jwtUser = $user;

        $token = $this->guard()->login($jwtUser);
        return response()->json(['token'=>$token], 201);
    }

    public function login(Request $request) {
        $credentials = $request->only('email','password');

        if(!$token = $this->guard()->attempt($credentials)) {
            return response()->json(['error'=>'Unauthorized'], 401);
        }

        return response()->json(['token'=>$token]);
    }

    public function me() {
        return response()->json($this->guard()->user());
    }

  public function logout(Request $request)
    {
        try {
            $this->guard()->invalidate(true);
        } catch (\Exception $e) {
            // token already invalid, continue
        }

        return redirect('/login');
    }
}