<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ApiController   extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/registeer",
     *     tags={"auth"},
     *     summary="Register a new user",
     *     description="Register a new user with the specified name, email, password, and role.",
     *     operationId="register",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="User's name",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="User's email",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="User's password",
     *                 ),
     *                 @OA\Property(
     *                     property="role",
     *                     type="string",
     *                     description="User's role (organizer or volunteer)",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     )
     * )
     */


    public function register(Request $request)
    {
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
     /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"auth"},
     *     summary="Login",
     *     description="Login with the specified email and password.",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="User's email",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="User's password",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *     )
     * )
     */

    public function login(Request $request)
    {
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
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"auth"},
     *     summary="Get user profile",
     *     description="Get the profile of the authenticated user.",
     *     operationId="profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */

    
    public function profile()
    {
        $userData = auth()->user();
        return response()->json([
            'status' => true,
            'message' => "profile data",
            "user" => $userData
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/refresh",
     *     tags={"auth"},
     *     summary="Refresh authentication token",
     *     description="Refresh the authentication token of the authenticated user.",
     *     operationId="refreshToken",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    
    public function refreshToken()
    {
        $newToken = auth()->refresh();
        return response()->json([
            'status' => true,
            'message' => "refreshed token",
            'token' => $newToken
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/logout",
     *     tags={"auth"},
     *     summary="Logout",
     *     description="Logout the authenticated user.",
     *     operationId="logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'status' => true,
            'message' => "user logged out successfully"
        ]);
    }
}
