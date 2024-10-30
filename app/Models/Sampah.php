<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Sampah extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'sampah';
    protected $primaryKey = 'id_sampah';

    protected $fillable = [
        'id_sampah', 'Nama_Sampah', 'Berat_Sampah', 'Point', 'id_jenis', 'id_penjemputan'
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisSampah::class, 'id_jenis', 'id_jenis');
    }

    public function penjemputan()
    {
        return $this->belongsTo(PenjemputanSampah::class, 'id_penjemputan', 'id_penjemputan');
    }
}
