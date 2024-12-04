<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dropbox extends Model
{
    use HasFactory;
    protected $table = 'dropbox';
    protected $primaryKey = 'dropbox_id';

    protected $fillable = [
        'name',
        'address',
        'district_address',
        'latitude',
        'longitude',
        'capacity'
    ];

    protected $hidden = [
        'status'
    ];
    public $incrementing = true;
}
