<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dropbox extends Model
{
    use HasFactory;
    protected $table = 'dropboxes';
    protected $primaryKey = 'id_dropbox';

    protected $fillable = [
        'Alamat', 'Longitude', 'Latitude', 'id_user'
    ];
    public $incrementing = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function penjemputanSampah()
    {
        return $this->hasMany(PenjemputanSampah::class, 'id_dropbox', 'id_dropbox');
    }
}
