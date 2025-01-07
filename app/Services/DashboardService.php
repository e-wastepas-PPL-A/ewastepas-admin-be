<?php

namespace App\Services;

use App\Jobs\SendEmailForgotPassword;
use App\Models\PasswordResetToken;
use App\Models\PickupWaste;
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

    public function getNotification()
    {
        try {
            $data = PickupWaste::query()
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();


            $response = [
                'notifications' => $data->map(function ($item) {
                    return [
                        'pickup_id' => $item->pickup_id,
                        'status' => $item->pickup_status,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                })
            ];

            return [true, 'Notifications', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
}
