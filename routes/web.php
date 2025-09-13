<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherKeyController;

Route::get('/', function () {
    return view('welcome');
});

// Teacher Keys routes - protected by authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/teacher-keys', [TeacherKeyController::class, 'index'])->name('teacher-keys.index');
    Route::post('/teacher-keys', [TeacherKeyController::class, 'store'])->name('teacher-keys.store');
    Route::put('/teacher-keys/{key}', [TeacherKeyController::class, 'update'])->name('teacher-keys.update');
    Route::delete('/teacher-keys/{key}', [TeacherKeyController::class, 'destroy'])->name('teacher-keys.destroy');
});
