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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;



Route::post('/register/student', [RegisterController::class, 'registerStudent']);
Route::post('/register/company', [RegisterController::class, 'registerCompany']);
Route::get('/company/activate/{id}', [RegisterController::class, 'activateCompany'])
    ->name('company.activate');

Route::middleware(['auth:api', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
});

// Verejný endpoint pre načítanie všetkých firiem (pre študentov)
Route::get('/companies', [CompanyController::class, 'list']);

Route::middleware(['auth:api', 'role:company'])->group(function () {
    Route::get('/company/dashboard', [CompanyController::class, 'dashboard']);
    Route::get('/company/internships/pending', [CompanyController::class, 'pendingInternships']);
    Route::get('/company/internships/approved', [CompanyController::class, 'approvedInternships']);
    Route::get('/company/internships/rejected', [CompanyController::class, 'rejectedInternships']);
    Route::get('/company/internships/{id}', [CompanyController::class, 'internshipDetail']);
    Route::post('/company/internships/{id}/approve', [CompanyController::class, 'approveInternship']);
    Route::post('/company/internships/{id}/reject', [CompanyController::class, 'rejectInternship']);
    Route::put('/company/internships/{id}/status', [CompanyController::class, 'updateStatus']);
});

Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']);
Route::post('/change-password', [LoginController::class, 'changePassword']);

Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

Route::middleware('auth:api')->get('/internships/my', [InternshipController::class, 'myInternships']);
Route::middleware('auth:api')->post('/internships/{id}/status', [InternshipController::class, 'changeStatus']);

Route::get('internships', [InternshipController::class, 'index']);
Route::get('internships/{id}', [InternshipController::class, 'show']);
Route::get('internships/user/{id}', [InternshipController::class, 'show']);
Route::post('internships', [InternshipController::class, 'store']);
Route::put('internships/{id}', [InternshipController::class, 'update']);
Route::delete('internships/{id}', [InternshipController::class, 'destroy']);

Route::middleware('auth:api')->post('/update-profile', [LoginController::class, 'updateProfile']);

Route::get('internships/{id}/agreement/download', [InternshipController::class, 'downloadAgreement']);



Route::get('/test-pdf', function () {
    $dummyData = [
        'internship' => (object)['start_date' => '2025-03-01', 'end_date' => '2025-06-30'],
        'student' => (object)['first_name' => 'Andrej', 'last_name' => 'Kováč', 'email' => 'andrej@example.com'],
        'company' => (object)['company_name' => 'SoftCorp s.r.o.', 'contact_person_name' => 'Ján Novák', 'contact_person_email' => 'jan@softcorp.sk'],
        'garant' => (object)['first_name' => 'Peter', 'last_name' => 'Horváth'],
    ];

    $pdf = Pdf::loadView('pdf.agreement', $dummyData);
    $path = 'test/test_dohoda.pdf';
    Storage::disk('public')->put($path, $pdf->output());

    return response()->json(['message' => 'PDF bolo vytvorené.', 'path' => "/storage/{$path}"]);
});



// EXTERNY SYSTEM
Route::middleware(['auth:api', 'role:external'])->group(function () {
    //Route::middleware('auth:api')->get('/external/internships/approved/', [InternshipController::class, 'getApprovedInternships']);
    Route::get('/external/internships/approved/', [InternshipController::class, 'getApprovedInternships']);
    Route::post('/external/internships/approved/{id}', [InternshipController::class, 'markAsDefended']);

});
