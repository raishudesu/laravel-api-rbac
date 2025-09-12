<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userExists = User::where('email', $validated['email'])->first();
        if ($userExists) {
            return response()->json([
                'message' => 'User already exists',
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => Role::where('slug', 'user')->first()?->id, // Default role
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
            'message' => 'User registered successfully',
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($validated)) {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 422);
        }
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token && $token instanceof PersonalAccessToken) {
            $token->delete();
        }


        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('role'),
        ]);
    }
}
