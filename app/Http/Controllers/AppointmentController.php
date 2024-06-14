<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;

use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // Admin: View all appointments
    public function index()
    {
        return Appointment::with('patient', 'doctor')->get();
    }

    // Doctor: Manage their own appointments
    public function doctorAppointments()
    {
        $doctorId = Auth::user()->id;
        return Appointment::with('patient')->where('doctor_id', $doctorId)->get();
    }

    public function store(Request $request)
{
    $user = Auth::user();
    \Log::info('Store appointment request', ['user' => $user, 'request' => $request->all()]);

    // Check if the authenticated user is a patient
    if ($user->role !== 'admin') {
        \Log::info('Forbidden: User role is not patient', ['user_role' => $user->role]);
        return response()->json(['message' => 'Forbidden'], 403);
    }

    // Validate the request
    $validated = $request->validate([
        'doctor_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'time' => 'required',
        'reason' => 'required|string',
    ]);

    // Create the appointment
    $appointment = new Appointment();
    $appointment->doctor_id = $request->doctor_id;
    $appointment->patient_id = Auth::id();
    $appointment->date = $request->date;
    $appointment->time = $request->time;
    $appointment->reason = $request->reason;
    $appointment->save();

    \Log::info('Appointment created', ['appointment' => $appointment]);

    return response()->json(['message' => 'Appointment booked successfully'], 200);
}

    // Patient: View their own appointments
    public function myAppointments()
    {
        $patientId = Auth::user()->id;
        return Appointment::with('doctor')->where('patient_id', $patientId)->get();
    }

    // Patient: Cancel their own appointment
    public function destroy($id)
    {
        $appointment = Appointment::where('id', $id)->where('patient_id', Auth::user()->id)->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found or not authorized'], 404);
        }

        $appointment->delete();
        return response()->json(['message' => 'Appointment canceled successfully'], 200);
    }

    // Doctor: Update their own appointments
    public function update(Request $request, $id)
    {
        $doctorId = Auth::user()->id;
        $appointment = Appointment::where('id', $id)->where('doctor_id', $doctorId)->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found or not authorized'], 404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'reason' => 'required|string'
        ]);

        $appointment->update($validated);
        return response()->json(['message' => 'Appointment updated successfully', 'appointment' => $appointment], 200);
    }
}

