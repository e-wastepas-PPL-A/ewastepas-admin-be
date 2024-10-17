<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids;
    public $incrementing = false;
    protected $fillable = ['product_category_id', 'store_id', 'title', 'thumbnail', 'price', 'is_active', 'validation', 'validation_note'];

    public function getThumbnail()
    {
        return sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $this->thumbnail);
    }

    public function linkProducts(): HasMany
    {
        return $this->hasMany(LinkProduct::class, 'product_id', 'id');
    }
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
