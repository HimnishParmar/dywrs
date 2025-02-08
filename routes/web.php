<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/waste/submit', [HomeController::class, 'showSubmissionForm'])->name('waste.submit');
Route::post('/waste/submit', [HomeController::class, 'submitWaste']);

Route::get('/waste/submit', [HomeController::class, 'showSubmissionForm'])->name('waste.submit');
Route::post('/waste/submit', [HomeController::class, 'submitWaste']);