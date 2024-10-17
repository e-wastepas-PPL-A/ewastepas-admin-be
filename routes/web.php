<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/api/');
Route::get('auth/google/callback', [AuthController::class, 'googleCallback']);

Route::fallback(static function () {
    return response()->json([
        'success' => false,
        'data' => [],
        'message' => 'Not found'
    ]);
});
