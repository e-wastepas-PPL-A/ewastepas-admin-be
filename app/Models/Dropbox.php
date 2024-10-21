<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dropbox extends Model
{
    use HasFactory;
    protected $table = 'dropboxes';

    protected $fillable = ['DropboxID', 'LocationName', 'Address', 'created_at', 'updated_at'];
    public $incrementing = true;
}
