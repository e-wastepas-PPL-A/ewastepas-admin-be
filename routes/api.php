<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DropboxController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);

        //> product category
        Route::prefix('product/category')->group(function () {
            Route::get('/', [ProductCategoryController::class, 'index']);
            Route::post('create', [ProductCategoryController::class, 'create']);
            Route::post('update/{id}', [ProductCategoryController::class, 'update']);
            Route::delete('delete/{id}', [ProductCategoryController::class, 'delete']);
            Route::get('{id}', [ProductCategoryController::class, 'show']);
        });

        //> product
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/create', [ProductController::class, 'create']);
            Route::post('/update/{id}', [ProductController::class, 'update']);
            Route::delete('/delete/{id}', [ProductController::class, 'delete']);
            Route::post('/update-status/{id}', [ProductController::class, 'updateStatus']);
            Route::get('/{id}', [ProductController::class, 'show']);
        });
    });

    //> profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'profile']);
        Route::post('/update-password', [ProfileController::class, 'updatePassword']);
    });
});

Route::prefix('admins')->group(function () {
    Route::get('', [AdminController::class, 'index']);
    Route::get('/{id}', [AdminController::class, 'show']);
    Route::post('/create', [AdminController::class, 'create']);
    Route::post('/update/{id}', [AdminController::class, 'update']);
    Route::post('/update-status/{id}', [AdminController::class, 'updateStatus']);
    Route::delete('/delete/{id}', [AdminController::class, 'delete']);
});

Route::get('/dropbox', [DropboxController::class, 'index']);
Route::get('/dropbox/{id}', [DropboxController::class, 'show']);
Route::post('/dropbox/create', [DropboxController::class, 'create']);
Route::post('/dropbox/update/{id}', [DropboxController::class, 'update']);
Route::delete('/dropbox/delete/{id}', [DropboxController::class, 'delete']);

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'ability:accessLoginMember']);
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

Route::get('/', static function () {
    return response()->json([
        'success' => true,
        'data' => [],
        'message' => 'Welcome Home'
    ]);
})->name('home');
