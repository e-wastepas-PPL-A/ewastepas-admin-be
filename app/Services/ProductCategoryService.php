<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductCategoryService
{
    public function listProductCategory($limit, $search)
    {
        try {
            $user = Auth()->user();

            $data = tap(
                ProductCategory::when($search, function ($query) use ($search) {
                    return $query->where("name", 'LIKE', "%$search%");
                })->where('store_id', $user->store->id)->orderBy('sequence', 'desc')
                    ->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'slug' => $item->slug,
                                'is_active' => ($item->is_active == 1),
                                'sequence' => $item->sequence,
                                'total_product' => $item->products->count()
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'product_categories' => $data
            ];
            return [true, 'List product category', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createProductCategory($data)
    {
        try {
            DB::beginTransaction();
            $user = Auth()->user();

            $seqeunce = ProductCategory::where([
                'store_id' => $user->store->id,
            ])->count();

            $productCategory = ProductCategory::where(['name' => $data['name'], 'store_id' => $user->store->id])->first();
            if ($productCategory) {
                return [false, 'Kategori sudah tersedia', []];
            }
            ProductCategory::create([
                'store_id' => $user->store->id,
                'name' => $data['name'],
                'is_active' => $data['is_active'],
                'sequence' => $seqeunce + 1,
                'slug' => str()->slug($data['name']),
            ]);
            DB::commit();
            return [true, 'Kategori berhasil tambahkan', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function updateProductCategory($data, $id)
    {
        try {
            $user = Auth()->user();

            $productCategory = ProductCategory::where(['id' => $id, 'store_id' => $user->store->id])->first();

            if (!$productCategory) {
                return [false, "Kategori tidak ditemukan", []];
            }

            if ($productCategory->name != $data['name']) {
                $checkProductCategory = ProductCategory::where(['name' => $data['name'], 'store_id' => $user->store->id])->first();
                if ($checkProductCategory) {
                    return [false, 'Kategori sudah tersedia', []];
                }
            }
            //> validasi link
            $productCategory->update([
                'name' => $data['name'],
                'is_active' => $data['is_active'],
                'slug' => str()->slug($data['name']),
            ]);

            return [true, 'Kategori berhasil diperbaharui', []];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function deleteProductCategory($id)
    {
        try {
            $user = Auth()->user();

            $data = ProductCategory::where(['id' => $id, 'store_id' => $user->store->id])->first();
            if ($data->products->count() > 0) {
                return [false, "Kategori tidak bisa dihapus, ada produk yang memiliki kategori ini", []];
            }
            if (!$data) {
                return [false, "Kategori tidak ditemukan", []];
            }

            $data->delete();

            return [true, 'Kategori berhasil dihapus', []];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function getDetailProductCategory($id)
    {
        try {
            $user = Auth()->user();

            $data = ProductCategory::where('store_id', $user->store->id)->find($id);

            if (!$data) {
                return [false, 'Data tidak ditemukan', []];
            }
            $response = [
                'id' => $data->id,
                'name' => $data->name,
                'slug' => $data->slug,
                'sequence' => $data->sequence,
                'is_active' => $data->is_active == 1,
            ];
            return [true, 'Detail kategori', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
}
