<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $fillable = ['id', 'store_id', 'name', 'slug', 'is_active', 'sequence'];

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }
}
