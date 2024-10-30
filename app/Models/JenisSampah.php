<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'jenis_sampah';
    protected $primaryKey = 'id_jenis';

    protected $fillable = [
        'id_jenis',
        'Nama_JenisSampah'
    ];

    public function sampah()
    {
        return $this->hasMany(Sampah::class, 'id_jenis', 'id_jenis');
    }
}
