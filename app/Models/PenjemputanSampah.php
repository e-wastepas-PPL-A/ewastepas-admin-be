<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjemputanSampah extends Model
{
    use HasFactory;

    protected $table = 'penjemputan_sampah';
    protected $primaryKey = 'id_penjemputan';

    protected $fillable = [
        'jumlah_sampah', 'tanggal_penjemputan', 'alamat_penjemputan',
        'status_penjemputan', 'status_pengiriman', 'total_sampah',
        'id_user', 'id_dropbox'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function dropbox()
    {
        return $this->belongsTo(Dropbox::class, 'id_dropbox', 'id_dropbox');
    }

    public function sampah()
    {
        return $this->hasMany(Sampah::class, 'id_penjemputan', 'id_penjemputan');
    }
}
