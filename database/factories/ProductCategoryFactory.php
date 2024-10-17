<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition()
    {
        return [
            'id' => (string) Str::uuid(), // Menggunakan UUID untuk 'id'
            'store_id' => \App\Models\Store::factory(), // Relasi dengan Store
            'name' => $this->faker->word, // Nama kategori
            'slug' => $this->faker->slug, // Slug kategori
            'is_active' => $this->faker->boolean, // Status aktif (boolean)
            'sequence' => $this->faker->numberBetween(1, 100), // Sequence
        ];
    }
}
