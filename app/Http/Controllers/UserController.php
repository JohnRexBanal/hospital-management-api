<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index($id)
    {
        $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json($user);
    }  

    public function getDoctors()
    {
        $doctors = User::with('doctor')->where('role', 'doctor')->get(['id', 'name', 'email', 'role']);
        return response()->json($doctors, 200);
    }

    public function getPatients()
    {
        $patients = User::with('patient')->where('role', 'patient')->get(['id', 'name', 'email', 'role']);
        return response()->json($patients, 200);
    }

    public function getMedicalRecords()
    {
        try {
            $patient = Patient::with('user')->findOrFail($id);
            return response()->json($patient, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Medical record not found'], 404);
        }
    }

    public function showDoctor($id)
    {
        $doctor = Doctor::with('user')->where('user_id', $id)->firstOrFail();
        return response()->json($doctor, 200);
    }


    public function showPatient($id)
    {
        $patient = Patient::with('user')->where('user_id', $id)->firstOrFail();
        return response()->json($patient, 200);
    }

    public function updateDoctor(Request $request, $id)
{
    // Get the authenticated user
    $user = Auth::user();

    // Check if the user is authorized to update the profile
    if ($user->role !== 'doctor' && $user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // If the user is a doctor, check if they are trying to update their own profile
    if ($user->role === 'doctor' && $user->id != $id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Validate the incoming request data
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($id)],
        'password' => 'sometimes|required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        'specialization' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:15'
    ]);

    try {
        // Find the user to update
        $userToUpdate = User::findOrFail($id);

        // Update user data
        $userToUpdate->name = $request->name;
        $userToUpdate->email = $request->email;

        if ($request->filled('password')) {
            $userToUpdate->password = Hash::make($request->password);
        }

        $userToUpdate->save();

        // Update doctor data
        $doctor = Doctor::where('user_id', $id)->firstOrFail();
        $doctor->specialization = $request->specialization;
        $doctor->address = $request->address;
        $doctor->phone = $request->phone;
        $doctor->save();

        return response()->json(['message' => 'Doctor updated successfully', 'doctor' => $doctor], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => 'User or doctor not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to update doctor: ' . $e->getMessage()], 500);
    }
}

    


public function updatePatient(Request $request, $id)
{
    // Find the patient by id
    $patient = Patient::find($id);

    // Check if patient exists
    if (!$patient) {
        return response()->json(['message' => 'Patient not found'], 404);
    }

    // Get the associated user
    $user = $patient->user;

    // Check if user exists
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Validate the request data
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
        'password' => 'sometimes|required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        'past_conditions' => 'nullable|string',
        'surgical_history' => 'nullable|string',
        'allergies' => 'nullable|string',
        'family_history' => 'nullable|string',
        'current_medications' => 'nullable|string',
    ]);

    // Update user data
    try {
        $user->name = $validated['name'] ?? $user->name;
        $user->email = $validated['email'] ?? $user->email;
        if (isset($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        $user->save();

        // Update patient medical records
        $patient->past_conditions = $validated['past_conditions'] ?? $patient->past_conditions;
        $patient->surgical_history = $validated['surgical_history'] ?? $patient->surgical_history;
        $patient->allergies = $validated['allergies'] ?? $patient->allergies;
        $patient->family_history = $validated['family_history'] ?? $patient->family_history;
        $patient->current_medications = $validated['current_medications'] ?? $patient->current_medications;
        $patient->save();

        return response()->json(['message' => 'Patient information updated successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred while updating the patient', 'error' => $e->getMessage()], 500);
    }
}




    public function deleteUser($id)
    {
        try {
            $user = User::find($id);
    
            if (!$user) 
            {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            $user->delete();
    
            return response()->json(['message' => 'User deleted successfully'], 200);

        } catch (\Exception $e) 
        {
            return response()->json(['message' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }

    public function countPatients()
    {
        $patients = User::where('role', 'patient')->count();
        return response()->json($patients, 200);
    }

    

    public function countDoctors()
    {
        $doctors = User::where('role', 'doctor')->count();
        return response()->json($doctors, 200);
    }

    public function recentDoctors()
    {
        $doctors = User::with('doctor')->where('role', 'doctor')->latest()->take(5)->get(['id', 'name', 'email', 'role']);
        return response()->json($doctors, 200);
    }

    public function recentPatients()
    {
        $patients = User::with('patient')->where('role', 'patient')->latest()->take(5)->get(['id', 'name', 'email', 'role']);
        return response()->json($patients, 200);
    }

    public function getPatientProfile(Request $request, $id)
    {
        $patient = User::join('patients', 'users.id', '=', 'patients.user_id')
                       ->where('users.id', $id)
                       ->where('users.role', 'patient')
                       ->select('users.*', 'patients.*')
                       ->first();

        if (!$patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        return response()->json($patient);
    }

    public function getDoctorProfile(Request $request, $id)
    {
        $doctor = User::join('doctors', 'users.id', '=', 'doctors.user_id')
                      ->where('users.id', $id)
                      ->where('users.role', 'doctor')
                      ->select('users.*', 'doctors.*')
                      ->first();

        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        return response()->json($doctor);
    }

}
