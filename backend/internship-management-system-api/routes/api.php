<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GarantController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\StudentController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DocumentController;

Route::post('/register/student', [RegisterController::class, 'registerStudent']);
Route::post('/register/company', [RegisterController::class, 'registerCompany']);
Route::get('/company/activate/{id}', [RegisterController::class, 'activateCompany'])
    ->name('company.activate');

Route::middleware(['auth:api', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
    Route::post('/student/company', [CompanyController::class, 'registerCompanyByStudent']);

    // Profil študenta – načítanie a úprava (pre stránku Nastavenia)
    Route::get('/student/profile', [StudentController::class, 'profile']);
    Route::put('/student/profile', [StudentController::class, 'updateProfile']);

    // Notifikácie – nastavenia (emailové preferencie)
    Route::get('/student/notifications', [StudentController::class, 'getNotifications']);
    Route::put('/student/notifications', [StudentController::class, 'updateNotifications']);

    Route::post('/internships/{id}/documents/upload', [DocumentController::class, 'uploadStudentDocument']);

    // Reálne notifikácie pre zvonček (študent)
    Route::get('/student/user-notifications', [StudentController::class, 'getUserNotifications']);
    Route::post('/student/notifications/read/{id}', [StudentController::class, 'markNotificationRead']);
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

    // Firemný profil – načítanie a úprava
    Route::get('/company/profile', [CompanyController::class, 'profile']);
    Route::put('/company/profile', [CompanyController::class, 'updateProfile']);

    // Notifikácie – načítanie a úprava
    Route::get('/company/notifications', [CompanyController::class, 'getNotifications']);
    Route::put('/company/notifications', [CompanyController::class, 'updateNotifications']);

    // Reálne notifikácie pre zvonček
    Route::get('/company/user-notifications', [CompanyController::class, 'getUserNotifications']);
    Route::post('/company/notifications/read/{id}', [CompanyController::class, 'markNotificationRead']);
});
Route::get('/internships/count/{status}', [GarantController::class, 'getCountByStatus']);

Route::middleware(['auth:api', 'role:garant'])->prefix('garant')->group(function () {

    Route::get('/dashboard', [GarantController::class, 'dashboard']);

    // Profil garanta – načítanie a úprava (pre stránku Nastavenia)
    Route::get('/profile', [GarantController::class, 'profile']);
    Route::put('/profile', [GarantController::class, 'updateProfile']);

    // Notifikácie – nastavenia (emailové preferencie)
    Route::get('/notifications', [GarantController::class, 'getNotifications']);
    Route::put('/notifications', [GarantController::class, 'updateNotifications']);

    // Zoznam praxí podľa stavu
    Route::get('/internships/status/{status}', [InternshipController::class, 'getByStatus']);
    Route::get('/internships/count/{status}', [InternshipController::class, 'getCountByStatus']);

    // Detail
    Route::get('/internships/{id}', [GarantController::class, 'internshipDetail']);

    // Garantove rozhodnutia
    Route::post('/internships/{id}/approve', [GarantController::class, 'approveInternship']);
    Route::post('/internships/{id}/disapprove', [GarantController::class, 'disapproveInternship']);

    // Obhajoby
    Route::post('/internships/{id}/defended', [GarantController::class, 'markDefended']);
    Route::post('/internships/{id}/not-defended', [GarantController::class, 'markNotDefended']);

    Route::put('internships/{id}', [InternshipController::class, 'update']);

    // Garant nahraje dokument k praxi
    Route::post('/internships/{id}/documents/upload', [DocumentController::class, 'uploadGarantDocument']);

    // Garant schváli dokument
    Route::post('/documents/{id}/approve', [DocumentController::class, 'approveDocumentByGarant']);

    // Garant zamietne dokument
    Route::post('/documents/{id}/reject', [DocumentController::class, 'rejectDocumentByGarant']);
    // Reálne notifikácie pre zvonček (garant)
    Route::get('/user-notifications', [GarantController::class, 'getUserNotifications']);
    Route::post('/notifications/read/{id}', [GarantController::class, 'markNotificationRead']);
});

Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']);
Route::post('/change-password', [LoginController::class, 'changePassword']);

Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

Route::middleware('auth:api')->get('/internships/my', [InternshipController::class, 'myInternships']);
Route::middleware('auth:api')->get('/internships/myNew', [InternshipController::class, 'myInternshipsNew']);

Route::middleware('auth:api')->post('/internships/{id}/status', [InternshipController::class, 'changeStatus']);

Route::get('internships', [InternshipController::class, 'index']);
Route::get('internships/{id}', [InternshipController::class, 'show']);
Route::get('internships/user/{id}', [InternshipController::class, 'show']);
Route::post('internships', [InternshipController::class, 'store']);
Route::put('internships/{id}', [InternshipController::class, 'update']);
Route::delete('internships/{id}', [InternshipController::class, 'destroy']);

Route::middleware('auth:api')->post('/update-profile', [LoginController::class, 'updateProfile']);

Route::middleware('auth:api')
    ->get('internships/{id}/agreement/download', [InternshipController::class, 'downloadAgreement']);




/* ============================================================================
 *    3 ROUTY PRE DOKUMENTY (list / download / delete)
 * ==========================================================================*/

Route::middleware('auth:api')
    ->get('/internships/{id}/documents', [DocumentController::class, 'listByInternship']);

Route::middleware('auth:api')
    ->get('/documents/{id}/download', [DocumentController::class, 'download']);

Route::middleware('auth:api')
    ->delete('/documents/{id}', [DocumentController::class, 'destroy']);



Route::get('/test-pdf', function () {
    $dummyData = [
        'internship' => (object) ['start_date' => '2025-03-01', 'end_date' => '2025-06-30'],
        'student' => (object) ['first_name' => 'Andrej', 'last_name' => 'Kováč', 'email' => 'andrej@example.com'],
        'company' => (object) ['company_name' => 'SoftCorp s.r.o.', 'contact_person_name' => 'Ján Novák', 'contact_person_email' => 'jan@softcorp.sk'],
        'garant' => (object) ['first_name' => 'Peter', 'last_name' => 'Horváth'],
    ];

    $pdf = Pdf::loadView('pdf.agreement', $dummyData);
    $path = 'test/test_dohoda.pdf';
    Storage::disk('public')->put($path, $pdf->output());

    return response()->json(['message' => 'PDF bolo vytvorené.', 'path' => "/storage/{$path}"]);
});

// EXTERNY SYSTEM
Route::middleware(['auth:api', 'role:external'])->group(function () {
    Route::get('/external/internships/approved/', [InternshipController::class, 'getApprovedInternships']);
    Route::post('/external/internships/approved/{id}', [InternshipController::class, 'markAsDefended']);
});

//ADMIN
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/users/', [AdminController::class, 'index']);
    Route::put('/admin/users/{id}', [AdminController::class, 'updateRoles']);
});

use App\Http\Controllers\GarantExportController;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/garant/export/preview', [GarantExportController::class, 'preview']);
    Route::get('/garant/export/csv', [GarantExportController::class, 'exportCsv']);
    Route::get('/garant/export/study-fields', [GarantExportController::class, 'studyFields']);
});
