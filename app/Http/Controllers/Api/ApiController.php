<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:organizer,volunteer',
        ]);
            User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => $request->role
        ]);
        return response()->json([
            'status' => true,
            'message' => "User created successfully"
        ]);
    }
    public function login(Request $request){
        $validatedData =  $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $token = JWTAuth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!empty($token)) {
            return response()->json([
                'status' => true,
                'message' => "User logged in successfully",
                'token' => $token
            ]); 
        }
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ]);
            
        }

    public function profile(){
        $userData = auth()->user();
        return response()->json([
            'status' => true,
            'message' => "profile data",
            "user" => $userData
        ]);

    }
    public function refreshToken(){
        $newToken = auth()->refresh();
        return response()->json([
            'status' => true,
            'message' => "refreshed token",
            'token' => $newToken
        ]);

    }
    public function logout(){
        auth()->logout();
        return response()->json([
            'status' => true,
            'message' => "user logged out successfully"
        ]);

    }
}
