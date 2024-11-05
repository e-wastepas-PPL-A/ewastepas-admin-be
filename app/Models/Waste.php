<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;

    protected $table = 'waste';
    protected $primaryKey = 'waste_id';

    protected $fillable = [
        'waste_name', 'point', 'waste_type_id', 'image', 'description', 'created_at', 'updated_at'
    ];

    public function wasteTypes()
    {
        return $this->belongsTo(WasteType::class, 'waste_type_id', 'waste_type_id');
    }

    public function pickupWastes()
    {
        return $this->belongsTo(PickupWaste::class, 'pickup_id', 'pickup_id');
    }
}
