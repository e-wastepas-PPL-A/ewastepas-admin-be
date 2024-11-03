<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierPoints extends Model
{
    use HasFactory;

    protected $table = 'courier_points';
    protected $primaryKey = 'points_id';

    protected $fillable = [
        'courier_id',
        'point',
        'created_at',
        'updated_at'
    ];

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id', 'courier_id');
    }
}
