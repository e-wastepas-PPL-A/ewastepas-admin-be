<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupWaste extends Model
{
    use HasFactory;

    protected $table = 'pickup_waste';

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id', 'courier_id');
    }
    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id', 'community_id');
    }
    public function pickupDetail()
    {
        return $this->hasMany(PickupDetail::class, 'pickup_id', 'pickup_id');
    }
    public function dropbox()
    {
        return $this->belongsTo(Dropbox::class, 'dropbox_id', 'dropbox_id');
    }
}
