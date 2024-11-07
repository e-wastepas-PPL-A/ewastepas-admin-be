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
        'latitude',
        'longitude',
        'capacity',
        'status'
    ];
    public $incrementing = true;
}
