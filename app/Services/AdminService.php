<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Admin;
use App\Models\LinkProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminService
{
    public function listAdmin($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Admin::when($search, function ($query) use ($search) {
                    return $query->where("name", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'email' => $item->email,
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'products' => $data
            ];
            return [true, 'List product', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createAdmin($data)
    {

        try {
            DB::beginTransaction();

            Admin::create([
                'id' => str()->uuid(),
                'name'  => $data['name'],
                'email'  => $data['email'],
                'password'  => Hash::make('pasword123'),
                'status' => 'ACTIVED'
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan admin', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    // public function updateProduct($data, $id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $user = auth()->user();
    //         $storeId = $user->store->id;
    //         $product = Product::where(['id' => $id, 'store_id' => $storeId])->first();
    //         if (!$product) {
    //             return [false, 'Produk tidak ditemukan', []];
    //         }
    //         //> check apakah katagori sudah ada
    //         if ($product?->productCategory?->name != $data['category']) {
    //             $productCategory = ProductCategory::where(['name' => $data['category'], 'store_id' => $storeId])->first();
    //             if (!$productCategory) {
    //                 $sequence = ProductCategory::where(['store_id' => $storeId])->count();
    //                 $productCategory = ProductCategory::create([
    //                     'name' => $data['category'],
    //                     'slug' => str()->slug($data['category']),
    //                     'store_id' => $storeId,
    //                     'sequence' => $sequence + 1
    //                 ]);
    //             }
    //         }



    //         $payload = [
    //             'product_category_id' => $productCategory->id,
    //             'title' => $data['title'],
    //             'price' => $data['price'],
    //         ];

    //         if (isset($data['thumbnail'])) {
    //             LinkyiStorage::deleteProductThumbnail($product->thumbnail);
    //             $payload['thumbnail'] = LinkyiStorage::uploadProductThumbnail($data['thumbnail']);
    //         }
    //         if (isset($data['is_active'])) {
    //             $payload['is_active'] = $data['is_active'];
    //         }
    //         //> create produk
    //         $product->update($payload);

    //         DB::commit();
    //         return [true, 'Berhasil Memperbaharui Produk', []];
    //     } catch (\Throwable $exception) {
    //         DB::rollBack();
    //         Log::error($exception);
    //         return [false, 'Server is busy right now!', []];
    //     }
    // }
    // public function updateStatusProduct($data, $id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $user = auth()->user();
    //         $storeId = $user->store->id;
    //         $product = Product::where(['id' => $id, 'store_id' => $storeId])->first();
    //         if (!$product) {
    //             return [false, 'Produk tidak ditemukan', []];
    //         }

    //         if ($data['is_active'] == 1) {
    //             $message = "diaktifkan";
    //         } else {
    //             $message = "dinonaktifkan";
    //         }
    //         //> create produk
    //         $product->update([
    //             'is_active' => $data['is_active'],
    //         ]);

    //         DB::commit();
    //         return [true, 'Produk berhasil ' . $message, []];
    //     } catch (\Throwable $exception) {
    //         DB::rollBack();
    //         Log::error($exception);
    //         return [false, 'Server is busy right now!', []];
    //     }
    // }

    public function detailAdmin($id)
    {
        $admin = Admin::where(['id' => $id])->first();
        if (!$admin) {
            return [false, 'Admin tidak ditemukan', []];
        }

        $response = [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
        ];
        return [true, "Detail admin", $response];
    }

    // public function deleteProduct($id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $user = Auth()->user();

    //         $product = Product::where(['id' => $id, 'store_id' => $user->store->id])->first();

    //         if (!$product) {
    //             return [false, "Produk tidak ditemukan", []];
    //         }

    //         //>habus total views & klik
    //         ProductView::where(['store_id' => $user->store->id, 'product_id' => $product->id])->delete();
    //         LinkProduct::where(['product_id' => $product->id])->delete();
    //         //>delete thumbnail
    //         LinkyiStorage::deleteProductThumbnail($product->thumbnail);
    //         $product->delete();
    //         DB::commit();
    //         return [true, 'Produk berhasil dihapus', []];
    //     } catch (\Throwable $exception) {
    //         DB::rollBack();
    //         Log::error($exception);
    //         return [false, 'Server is busy right now!', []];
    //     }
    // }

}
