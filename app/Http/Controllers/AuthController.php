<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Registration Request:', $request->all());
            // Validation
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Check if the password is in the common passwords list
            $commonPasswordsJson = file_get_contents(storage_path('app/commonPasswords.json'));
            $commonPasswords = json_decode($commonPasswordsJson, true);
            if (in_array($validatedData['password'], $commonPasswords)) {
                return response()->json(['error' => 'This password is too common. Please choose a more secure password.'], 422);
            }

            // Create the user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'previous_passwords' => [Hash::make($validatedData['password'])],
                'password_changed_at' => now(),
            ]);

            // Generate an access token
            // $token = $user->createToken('authToken')->accessToken;

            // Log generated token
            // Log::info('Generated Token:', ['token' => $token]);

            // Return the token and user data in the response
            return response()->json([
                // 'token' => $token,
                'user' => $user,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation Error', 'messages' => $e->errors()], 422);
        } catch (\Laravel\Passport\Exceptions\OAuthServerException $e) {
            Log::error('OAuth error: ' . $e->getMessage());
            return response()->json(['error' => 'Token generation failed.'], 500);
        } catch (\Exception $e) {
            Log::error('General error in registration: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validate the incoming request
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Attempt authentication
            if (!FacadesAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid email or password'], 401);
            }

            // Get the authenticated user
            $user = FacadesAuth::user();

            // Generate a token using Laravel's built-in token generation
            $token = $user->createToken('authToken')->accessToken;

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return response()->json(['error' => 'Login failed. Please try again later.'], 500);
        }
    }

}
