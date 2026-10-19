<?php

use App\Http\Controllers\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta garantizada para servir imágenes de notas directamente en el navegador con CORS
Route::get('/storage/notes/{filename}', [ImageUploadController::class, 'serve']);
