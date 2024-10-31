<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\ResponseResource;

class AuthController extends Controller
{
    public function registration(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:255',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('apptoken');

        $userResponse = [
            'name' => $user->name,
            'email' => $user->email,
            'token' => $token->plainTextToken
        ];

        return new ResponseResource(true, 'User created', $userResponse, [
            'code' => 201
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!auth()->attempt($credentials)) {
            return new ResponseResource(false, 'Invalid credentials', null, [
                'code' => 401
            ], 401);
        }

        $user = auth()->user();

        if ($user->tokens()->count() > 0) {
            $user->tokens()->delete();
        }

        $userResponse = [
            'name' => $user->name,
            'email' => $user->email,
            'token' => $user->createToken('apptoken', ['*'], now()->addWeek())->plainTextToken
        ];

        return new ResponseResource(true, 'Login success', $userResponse, [
            'expires_at' => $user->tokens()->first()->expires_at
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return new ResponseResource(true, 'Logout success', null, [
            'code' => 200
        ], 200);
    }
}
