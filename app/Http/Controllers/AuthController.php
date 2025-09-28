<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\LoginService;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected RegistrationService $registrationService;

    protected LoginService $loginService;

    public function __construct(RegistrationService $registrationService, LoginService $loginService)
    {
        $this->registrationService = $registrationService;
        $this->loginService = $loginService;
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $result = $this->registrationService->register($validated);

        return response()->json($result, 201);
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request)
    {

        $validated = $request->validated();

        $result = $this->loginService->login($validated);

        return response()->json($result);

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
