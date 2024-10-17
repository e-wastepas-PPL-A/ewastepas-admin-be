<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResset extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'password_reset_tokens';

    protected $fillable = ['email', 'token'];

    public $timestamps = false;
}
