<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

// Authentication routes

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/v/{id}', [SpaceController::class, 'view']);
Route::get('/d/{id}', [SpaceController::class, 'download']);

Route::get('/', [SpaceController::class, 'index'])->name('file.home');
Route::get('/{id}', [SpaceController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::post('/upload-file', [SpaceController::class, 'store'])->name('file.store');
    Route::post('/delete-file', [SpaceController::class, 'delete'])->name('file.delete');
    Route::post('/create-folder', [SpaceController::class, 'create_folder'])->name('file.folder');
    Route::post('/share-folder', [SpaceController::class, 'share'])->name('folder.share');
});
