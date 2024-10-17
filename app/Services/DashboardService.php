<?php

namespace App\Services;

use App\Jobs\SendEmailForgotPassword;
use App\Models\PasswordResetToken;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\StoreVisitor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class DashboardService
{

    public function getDashboardSummary()
    {
        try {
            $user = Auth()->user();
            $response = [
                'visitors' => [
                    'product' => Product::where(['store_id' => $user->store->id, 'is_active' => 1])->count(),
                    'visitor' => ProductView::where(['store_id' => $user->store->id])->sum('views'),
                    'category' => ProductCategory::where(['store_id' => $user->store->id, 'is_active' => 1])->count(),
                    'total_click' => ProductView::where(['store_id' => $user->store->id])->sum('views')
                ]
            ];

            return [true, 'Dashboard', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
}
