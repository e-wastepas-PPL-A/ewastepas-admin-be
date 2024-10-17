<?php

namespace App\Helpers;

use App\Models\Store;
use App\Models\StoreTheme;
use App\Models\StoreVerification;
use App\Models\StoreView;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UnitTestHelper
{
    public static function initUserLogin()
    {
        $user = User::factory()->create();

        // Data Faker untuk store
        $data = [
            'name' => 'Electronics',
            'slug' => Str()->slug('Electronics'),
            'description' => 'Store description',
            'logo' => 'logo.png',
        ];

        // Buat Store
        $store = Store::create([
            'user_id' => $user['id'],
            'name' => trim($data['name']),
            'slug' => $data['slug'],
            'logo' => $data['logo'],
            'description' => $data['description']
        ]);

        // Buat StoreView, StoreTheme, StoreVerification
        StoreView::create(['store_id' => $store->id, 'total' => 0]);
        StoreTheme::create(['store_id' => $store->id, 'theme_id' => str()->uuid(), 'type' => StoreTheme::TYPE_FREE]);
        StoreVerification::create(['store_id' => $store->id, 'status' => StoreVerification::STATUS_UNVERIFIED]);
        Auth::loginUsingId($user['id']);
        return $user;
    }
}
