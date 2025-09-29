<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * Register a new user.
     *
     * @throws ValidationException
     */
    public function register(array $validatedData): array
    {
        try {
            Log::info('Registration attempt started', ['email' => $validatedData['email']]);

            // Check if user already exists
            $userExists = $this->emailExists($validatedData['email']);

            if ($userExists) {
                Log::warning('Registration failed: User already exists', ['email' => $validatedData['email']]);

                throw ValidationException::withMessages([
                    'email' => ['The email has already been taken.'],
                ]);
            }

            // Get the default user role
            $defaultRole = $this->getDefaultRole();

            if (! $defaultRole) {
                Log::error('Registration failed: Default user role not found');

                throw ValidationException::withMessages([
                    'system' => ['Unable to assign default role. Please contact support.'],
                ]);
            }

            // Create the user
            $user = $this->createUser($validatedData, $defaultRole);

            // Create authentication token
            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'user' => $user->load('role'),
                'token' => $token,
                'message' => 'User registered successfully',
            ];

        } catch (ValidationException $e) {
            // Re-throw validation exceptions as they are
            throw $e;
        } catch (\Exception $e) {
            Log::error('Registration failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $validatedData['email'] ?? 'unknown',
            ]);

            throw ValidationException::withMessages([
                'system' => ['An unexpected error occurred. Please try again.'],
            ]);
        }
    }

    /**
     * Get the default user role.
     */
    private function getDefaultRole(): ?Role
    {
        return Role::where('slug', 'user')->first();
    }

    /**
     * Create a new user.
     */
    private function createUser(array $validatedData, Role $role): User
    {
        return User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $role->id,
        ]);
    }

    /**
     * Check if email is already registered.
     */
    private function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Get registration statistics.
     */
    public function getRegistrationStats(): array
    {
        $totalUsers = User::count();
        $recentRegistrations = User::where('created_at', '>=', now()->subDays(7))->count();

        return [
            'total_users' => $totalUsers,
            'recent_registrations' => $recentRegistrations,
            'last_week' => $recentRegistrations,
        ];
    }
}
