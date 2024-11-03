<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Courier extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'courier';
    protected $primaryKey = 'courier_id';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'date_of_birth',
        'address', 'account_number', 'nik', 'ktp_url', 'kk_url', 
        'photo', 'is_verified', 'is_active',
        'otp_code', 'otp_expiry', 'created_at', 'updated_at'
    ];

    // const STATUS_VERIFIED = 'verified';
    // const STATUS_UNVERIFIED = 'unverified';
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    public $incrementing = true;
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password' => 'hashed',
    //     ];
    // }

    public function courierPoints(): HasOne
    {
        return $this->hasOne(CourierPoints::class, 'courier_id', 'courier_id');
    }

    public function pickupWastes(): HasMany
    {
        return $this->hasMany(PickupWaste::class, 'courier_id', 'courier_id');
    }
}
