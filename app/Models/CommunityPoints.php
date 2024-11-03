<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPoints extends Model
{
    use HasFactory;

    protected $table = 'community_points';
    protected $primaryKey = 'points_id';

    protected $fillable = [
        'community_id',
        'point',
        'created_at',
        'updated_at'
    ];

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id', 'community_id');
    }
}
