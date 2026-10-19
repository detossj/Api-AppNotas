<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;


// Rutas públicas de autenticación y recursos estáticos
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/images/{filename}', [ImageUploadController::class, 'serve']);

// Rutas privadas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Subida de imágenes para notas
    Route::post('/upload-image', [ImageUploadController::class, 'upload']);

    // CRUD completo para categorías y notas
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('notes', NoteController::class);
});
