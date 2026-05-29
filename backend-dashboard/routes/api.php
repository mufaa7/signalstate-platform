<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;

// 1. Rute bawaan Laravel (Biarkan saja)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 2. JALUR UTAMA BOT PYTHON (Tambahkan ini di paling bawah)
// Jalur ini steril dari session/cookie web dan langsung mengarah ke controller kita
Route::post('/posts', [PostController::class, 'store']);