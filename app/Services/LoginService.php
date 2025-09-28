<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function login(array $validatedData)
    {

        try {
            Log::info('Login attempt started', ['email' => $validatedData['email']]);

            if (! Auth::attempt($validatedData)) {
                Log::warning('Login failed: Invalid credentials', ['email' => $validatedData['email']]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User logged in successfully', ['user_id' => $user->id, 'email' => $user->email]);

            return [
                'user' => $user->load('role'),
                'token' => $token,
                'message' => 'Login successful',
            ];

        } catch (ValidationException $e) {
            // Re-throw validation exceptions as they are
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $validatedData['email'] ?? 'unknown',
            ]);
        }

    }
}
