<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- pôvodné (budúce) controllery – môžu ostať use, nič tým nepokazíš ---
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CompanyController;

// Route::post('/register/student', [RegisterController::class, 'registerStudent']);
// Route::post('/register/company', [RegisterController::class, 'registerCompany']);
// Route::get('/company/activate/{id}', [RegisterController::class, 'activateCompany'])
//     ->name('company.activate');

// Route::middleware(['auth:api', 'role:student'])->group(function () {
//     Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
// });

// Route::middleware(['auth:api', 'role:company'])->group(function () {
//     Route::get('/company/dashboard', [CompanyController::class, 'dashboard']);
// });

// Route::post('/login', [LoginController::class, 'login']);
// Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']);
// Route::middleware('auth:api')->post('/change-password', [LoginController::class, 'changePassword']);

// Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);
// Route::post('/reset-password', [LoginController::class, 'resetPassword']);


/**
 * ==========================================================
 *  TEMP (FUNKČNÉ) ENDPOINTY – fungujú hneď teraz
 *  (rovnaké pathy, žiadne auth/role, žiadne 500)
 * ==========================================================
 */

// SCRUM-10 – študentská registrácia (demo – už máš otestované 201 Created)
Route::post('/register/student', function (Request $request) {
    return response()->json([
        'message'  => 'OK – student registered (demo)',
        'payload'  => $request->all(),
    ], 201);
});

// SCRUM-11 – firemná registrácia (demo)
Route::post('/register/company', function (Request $request) {
    return response()->json([
        'message' => 'OK – company registered (demo)',
        'payload' => $request->all(),
    ], 201);
});

// Aktivácia firmy (demo)
Route::get('/company/activate/{id}', function ($id) {
    return response()->json([
        'message' => "Company {$id} activated (demo)"
    ], 200);
})->name('company.activate');

// Auth (demo) – aby trasy existovali a nezrážali 500
Route::post('/login',              fn () => response()->json(['message' => 'Logged in (demo)'], 200));
Route::post('/logout',             fn () => response()->json(['message' => 'Logged out (demo)'], 200));
Route::post('/change-password',    fn () => response()->json(['message' => 'Password changed (demo)'], 200));
Route::post('/forgot-password',    fn () => response()->json(['message' => 'Reset link sent (demo)'], 200));
Route::post('/reset-password',     fn () => response()->json(['message' => 'Password reset (demo)'], 200));

// Dashboardy (demo) – bez auth, len nech endpointy žijú
Route::get('/student/dashboard', fn () => response()->json(['message' => 'Student dashboard (demo)'], 200));
Route::get('/company/dashboard', fn () => response()->json(['message' => 'Company dashboard (demo)'], 200));
Route::get('/register/student', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API endpoint pre registráciu študenta je pripravený. Použi POST metódu na odoslanie dát.'
    ], 200);
});
