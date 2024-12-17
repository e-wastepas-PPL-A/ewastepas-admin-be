<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupDetail extends Model
{
    use HasFactory;

    protected $table = 'pickup_detail';

    public function waste()
    {
        return $this->belongsTo(Waste::class, 'waste_id', 'waste_id');
    }
    public function pickupWaste()
    {
        return $this->belongsTo(PickupWaste::class, 'pickup_id', 'pickup_id');
    }
}
