<?php
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Auth\LoginController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use App\Http\Controllers\InternshipController;




Route::post('/register/student', [RegisterController::class, 'registerStudent']);
Route::post('/register/company', [RegisterController::class, 'registerCompany']);
Route::get('/company/activate/{id}', [RegisterController::class, 'activateCompany'])
    ->name('company.activate');

Route::middleware(['auth:api', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
});

Route::middleware('auth:api')->get('/internships/my', [InternshipController::class, 'myInternships']);


// Verejný endpoint pre načítanie všetkých firiem (pre študentov)
Route::get('/companies', [CompanyController::class, 'list']);

Route::middleware(['auth:api', 'role:company'])->group(function () {
    Route::get('/company/dashboard', [CompanyController::class, 'dashboard']);
});

Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']);
Route::post('/change-password', [LoginController::class, 'changePassword']);

Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);


Route::get('internships', [InternshipController::class, 'index']);
Route::get('internships/{id}', [InternshipController::class, 'show']);
Route::get('internships/user/{id}', [InternshipController::class, 'show']);
Route::post('internships', [InternshipController::class, 'store']);
Route::put('internships/{id}', [InternshipController::class, 'update']);
Route::delete('internships/{id}', [InternshipController::class, 'destroy']);

Route::middleware('auth:api')->post('/update-profile', [LoginController::class, 'updateProfile']);
