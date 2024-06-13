    <?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\UserController;
    use App\Http\Middleware\CheckRole;
    use App\Http\Controllers\AppointmentController;

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });

    // admin routes | protected routes
    Route::middleware(['auth:sanctum', CheckRole::class . ':admin,doctor'])->group(function () {
        Route::get('/users/{id}', [UserController::class, 'index']);
        // doctor routes
        Route::get('/doctor/show/{id}', [UserController::class, 'showDoctor']);
        Route::put('/doctor/update/{id}', [UserController::class, 'updateDoctor']);
        Route::get('/doctors/count', [UserController::class, 'countDoctors']);
        Route::get('/doctors/recent', [UserController::class, 'recentDoctors']);
        Route::delete('/user/delete/{id}', [UserController::class, 'deleteUser']);
        // patient routes
        Route::get('/patients/list', [UserController::class, 'getPatients']);
        Route::put('/patients/update/{id}', [UserController::class, 'updatePatient']);
        Route::get('/patients/show/{id}', [UserController::class, 'showPatient']);
        Route::get('/medical/records/', [UserController::class, 'getMedicalRecords']);
        Route::get('/patients/count', [UserController::class, 'countPatients']);
        Route::get('/patients/recent', [UserController::class, 'recentPatients']);

        Route::get('/doctor/profile/{id}', [UserController::class, 'getDoctorProfile']);


        // appointment routes
        Route::get('/doctor/appointments', [AppointmentController::class, 'doctorAppointments']);
        Route::post('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::get('/appointments/list', [AppointmentController::class, 'index']);

        
    });

    Route::middleware(['auth:sanctum', CheckRole::class . ':patient'])->group(function () {
        Route::get('/patient/profile/{id}', [UserController::class, 'getPatientProfile']);
        Route::post('/appointments/create', [AppointmentController::class, 'store']);
        Route::get('/appointments/mine', [AppointmentController::class, 'myAppointments']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    });


    // public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/doctors/list', [UserController::class, 'getDoctors']);

        
        