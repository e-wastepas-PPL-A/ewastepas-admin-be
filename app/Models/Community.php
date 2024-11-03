<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class Community extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'community';
    protected $primaryKey = 'community_id';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'date_of_birth',
        'address', 'photo', 'is_verified', 'otp_code', 'otp_expiry',
        'created_at', 'updated_at'
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

    public function communityPoints(): HasOne
    {
        return $this->hasOne(CommunityPoints::class, 'community_id', 'community_id');
    }

    public function pickupWastes(): HasMany
    {
        return $this->hasMany(PickupWaste::class, 'community_id', 'community_id');
    }

    // Fungsi sementara untuk otp
    public function generateOtp()
    {
        $this->otp_code = rand(100000, 999999);
        $this->otp_expiry = Carbon::now()->addMinutes(5);
        $this->save();
    }

    public function verifyOtp($inputOtp)
    {
        return $this->otp_code === $inputOtp && $this->otp_expiry && $this->otp_expiry->isFuture();
    }
}
