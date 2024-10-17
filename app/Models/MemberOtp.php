<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberOtp extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['email', 'code', 'type'];
    public $incrementing = false;

    const TYPE_ACTIVATION = 'activation';
    const TYPE_FORGOTPASSWORD = 'forgot-password';
}
