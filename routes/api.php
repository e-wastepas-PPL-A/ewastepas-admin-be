<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\WasteController;
use App\Http\Controllers\WasteConvertController;
use App\Http\Controllers\WasteTypeController;
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

// Route::post('community/send-otp', [CommunityController::class, 'sendOtp']);
// Route::post('community/verify-otp', [CommunityController::class, 'verifyOtp']);

Route::prefix('community')->group(function () {
    Route::get('', [CommunityController::class, 'index']);
    Route::get('/{id}', [CommunityController::class, 'show']);
    Route::post('/create', [CommunityController::class, 'create']);
    Route::post('/update/{id}', [CommunityController::class, 'update']);
    Route::post('/update-status/{id}', [CommunityController::class, 'updateStatus']);
    Route::delete('/delete/{id}', [CommunityController::class, 'delete']);
});

Route::prefix('courier')->group(function () {
    Route::get('', [CourierController::class, 'index']);
    Route::get('/{id}', [CourierController::class, 'show']);
    Route::post('/create', [CourierController::class, 'create']);
    Route::post('/update/{id}', [CourierController::class, 'update']);
    Route::post('/update-status/{id}', [CourierController::class, 'updateStatus']);
    Route::delete('/delete/{id}', [CourierController::class, 'delete']);
});

Route::prefix('waste')->group(function () {
    Route::get('', [WasteController::class, 'index']);
    Route::get('/{id}', [WasteController::class, 'show']);
    Route::post('/create', [WasteController::class, 'create']);
    Route::post('/update/{id}', [WasteController::class, 'update']);
    Route::delete('/delete/{id}', [WasteController::class, 'delete']);
});

Route::prefix('waste_convert')->group(function () {
    Route::get('', [WasteConvertController::class, 'index']);
    Route::get('/{id}', [WasteConvertController::class, 'show']);
    Route::post('/update/{id}', [WasteConvertController::class, 'update']);
});

Route::prefix('waste_type')->group(function () {
    Route::get('', [WasteTypeController::class, 'index']);
    Route::get('/{id}', [WasteTypeController::class, 'show']);
    Route::post('/create', [WasteTypeController::class, 'create']);
    Route::post('/update/{id}', [WasteTypeController::class, 'update']);
    Route::delete('/delete/{id}', [WasteTypeController::class, 'delete']);
});

Route::prefix('dropbox')->group(function () {
    Route::get('', [DropboxController::class, 'index']);
    Route::get('/{id}', [DropboxController::class, 'show']);
    Route::post('/create', [DropboxController::class, 'create']);
    Route::post('/update/{id}', [DropboxController::class, 'update']);
    Route::delete('/delete/{id}', [DropboxController::class, 'delete']);
});

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
