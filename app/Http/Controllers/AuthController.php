<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempting authentication with provided email and password
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $tokenName = $user->role . '-token';
            $token = $user->createToken($tokenName)->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    // Add other necessary user data here
                ],
            ], 201);
        }
        // If authentication fails, redirect back with input and a warning message
        return response()->json(['message' => 'Invalid username or password'], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
            if ($user) {
                $user->tokens()->delete();
                return response()->json(['message' => 'Logged out'], 200);
            } else {
                return response()->json(['message' => 'No authenticated user'], 401);
            }
    }

    public function register(Request $request)
    {
        Log::info('Request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:doctor,patient',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($user->role === 'doctor') {
            $validator = Validator::make($request->all(), [
                'specialization' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            Doctor::create([
                'user_id' => $user->id,
                'specialization' => $request->specialization,
                'address' => $request->address,
                'phone' => $request->phone,
            ]);
        } elseif ($user->role === 'patient') {
            $validator = Validator::make($request->all(), [
                'dob' => 'required|date',
                'gender' => 'required|string|max:10',
                'contact_number' => 'required|string|max:15',
                'past_conditions' => 'nullable|string',
                'surgical_procedures' => 'nullable|string',
                'allergies' => 'nullable|string',
                'family_history' => 'nullable|string',
                'current_medications' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            Patient::create([
                'user_id' => $user->id,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'contact_number' => $request->contact_number,
                'past_conditions' => $request->past_conditions,
                'surgical_history' => $request->surgical_history,
                'allergies' => $request->allergies,
                'family_history' => $request->family_history,
                'current_medications' => $request->current_medications,
            ]);
        }

        return response()->json(['message' => 'User registered successfully'], 201);
    }
}
