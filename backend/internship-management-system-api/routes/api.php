<?php
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/register/student', [RegisterController::class, 'registerStudent']);
