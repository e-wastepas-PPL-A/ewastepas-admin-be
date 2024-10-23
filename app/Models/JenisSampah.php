<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    use HasFactory;

    protected $table = 'jenis_sampah';
    protected $primaryKey = 'id_jenis';

    protected $fillable = [
        'Nama_JenisSampah'
    ];

    public function sampah()
    {
        return $this->hasMany(Sampah::class, 'id_jenis', 'id_jenis');
    }
}
