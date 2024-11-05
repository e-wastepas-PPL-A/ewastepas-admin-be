<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    use HasFactory;

    protected $table = 'waste_type';
    protected $primaryKey = 'waste_type_id';

    protected $fillable = [
        'waste_type_name',
        'image',
        'created_at',
        'updated_at',
    ];

    public function wastes()
    {
        return $this->hasMany(Waste::class, 'waste_type_id', 'waste_type_id');
    }
}
