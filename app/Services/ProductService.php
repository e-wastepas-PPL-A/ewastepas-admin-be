<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\LinkProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function listProductWithPagination($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Product::when($search, function ($query) use ($search) {
                    return $query->where("title", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->where('store_id', $user->store->id)
                    ->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'id' => $item->id,
                                'title' => $item->title,
                                'price' => $item->price,
                                'thumbnail' => $item->getThumbnail(),
                                'category' => $item?->productCategory?->name,
                                'is_active' => ($item->is_active == 1),
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

    public function createProduct($data)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $storeId = $user->store->id;
            //> check apakah katagori sudah ada
            $productCategory = ProductCategory::where(['name' => $data['category'], 'store_id' => $storeId])->first();
            if (!$productCategory) {
                $sequence = ProductCategory::where(['store_id' => $storeId])->count();
                $productCategory = ProductCategory::create([
                    'name' => $data['category'],
                    'slug' => str()->slug($data['category']),
                    'store_id' => $storeId,
                    'sequence' => $sequence + 1
                ]);
            }

            $thumbnail = LinkyiStorage::uploadProductThumbnail($data['thumbnail']);
            //> create produk
            $product = Product::create([
                'product_category_id' => $productCategory->id,
                'store_id' => $storeId,
                'title' => $data['title'],
                'thumbnail' => $thumbnail,
                'price' => $data['price'],
                'is_active' => $data['is_active'],
            ]);
            //> save link
            foreach ($data['links'] as $link) {
                $type = LinkHelper::getDomainName($link);
                LinkProduct::create([
                    'product_id'    => $product->id,
                    'link'          => $link,
                    'views'         => 0,
                    'type'          => $type
                ]);
            }
            ProductView::create([
                'store_id'  => $storeId,
                'product_id' => $product->id,
                'views'     => 0
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan Produk', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function updateProduct($data, $id)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $storeId = $user->store->id;
            $product = Product::where(['id' => $id, 'store_id' => $storeId])->first();
            if (!$product) {
                return [false, 'Produk tidak ditemukan', []];
            }
            //> check apakah katagori sudah ada
            if ($product?->productCategory?->name != $data['category']) {
                $productCategory = ProductCategory::where(['name' => $data['category'], 'store_id' => $storeId])->first();
                if (!$productCategory) {
                    $sequence = ProductCategory::where(['store_id' => $storeId])->count();
                    $productCategory = ProductCategory::create([
                        'name' => $data['category'],
                        'slug' => str()->slug($data['category']),
                        'store_id' => $storeId,
                        'sequence' => $sequence + 1
                    ]);
                }
            }



            $payload = [
                'product_category_id' => $productCategory->id,
                'title' => $data['title'],
                'price' => $data['price'],
            ];

            if (isset($data['thumbnail'])) {
                LinkyiStorage::deleteProductThumbnail($product->thumbnail);
                $payload['thumbnail'] = LinkyiStorage::uploadProductThumbnail($data['thumbnail']);
            }
            if (isset($data['is_active'])) {
                $payload['is_active'] = $data['is_active'];
            }
            //> create produk
            $product->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Produk', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function updateStatusProduct($data, $id)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $storeId = $user->store->id;
            $product = Product::where(['id' => $id, 'store_id' => $storeId])->first();
            if (!$product) {
                return [false, 'Produk tidak ditemukan', []];
            }

            if ($data['is_active'] == 1) {
                $message = "diaktifkan";
            } else {
                $message = "dinonaktifkan";
            }
            //> create produk
            $product->update([
                'is_active' => $data['is_active'],
            ]);

            DB::commit();
            return [true, 'Produk berhasil ' . $message, []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailProduk($id)
    {
        $user = auth()->user();
        $storeId = $user->store->id;
        $product = Product::where(['id' => $id, 'store_id' => $storeId])->first();
        if (!$product) {
            return [false, 'Produk tidak ditemukan', []];
        }
        $response = [
            'title' => $product->title,
            'thumbnail' => $product->getThumbnail(),
            'price' => $product->price,
            'is_active' => $product->is_active == 1,
            'links' => $product->linkProducts->map(function ($item) {
                return [
                    'id' => $item->id,
                    'link' => $item->link,
                    'type' => $item->type,
                ];
            })
        ];
        return [true, "Detail Produk", $response];
    }

    public function deleteProduct($id)
    {
        try {
            DB::beginTransaction();
            $user = Auth()->user();

            $product = Product::where(['id' => $id, 'store_id' => $user->store->id])->first();

            if (!$product) {
                return [false, "Produk tidak ditemukan", []];
            }

            //>habus total views & klik
            ProductView::where(['store_id' => $user->store->id, 'product_id' => $product->id])->delete();
            LinkProduct::where(['product_id' => $product->id])->delete();
            //>delete thumbnail
            LinkyiStorage::deleteProductThumbnail($product->thumbnail);
            $product->delete();
            DB::commit();
            return [true, 'Produk berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    //> product links
    public function deleteProductLink($id, $link)
    {
        try {
            DB::beginTransaction();
            $user = Auth()->user();

            $product = Product::where(['id' => $id, 'store_id' => $user->store->id])->first();

            if (!$product) {
                return [false, "Produk tidak ditemukan", []];
            }

            $link = LinkProduct::where(['id' => $link, 'product_id' => $product->id])->first();

            if (!$link) {
                return [false, "Link tidak ditemukan", []];
            }

            $link->delete();
            DB::commit();
            return [true, 'Link produk berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function cretaeProductLink($data, $id)
    {
        try {
            DB::beginTransaction();
            $user = Auth()->user();
            $product = Product::where(['id' => $id, 'store_id' => $user->store->id])->first();

            if (!$product) {
                return [false, "Produk tidak ditemukan", []];
            }
            $type = LinkHelper::getDomainName($data['link']);
            LinkProduct::create(['type' => $type, 'link' => $data['link'], 'product_id' => $product->id, 'views' => 0]);

            DB::commit();
            return [true, 'Berhasil Menambahkan Link produk ', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function updateProductLink($data, $id, $link)
    {
        try {
            DB::beginTransaction();
            $user = Auth()->user();
            $product = Product::where(['id' => $id, 'store_id' => $user->store->id])->first();

            if (!$product) {
                return [false, "Produk tidak ditemukan", []];
            }
            $link = LinkProduct::where(['id' => $link, 'product_id' => $product->id])->first();

            if (!$link) {
                return [false, "Link tidak ditemukan", []];
            }

            $type = LinkHelper::getDomainName($data['link']);
            $link->update(['type' => $type, 'link' => $data['link']]);

            DB::commit();
            return [true, 'Link produk berhasil disimpan', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    //> public products
    public function listStoreProductWithPagination($slug, $limit, $search, $filter)
    {
        try {
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }
            $storeProfile = Store::where(['slug' => $slug])->first();

            if (!$storeProfile) {
                return [false, 'Data tidak ditemukan', []];
            }

            $data = tap(
                Product::when($search, function ($query) use ($search) {
                    return $query->where("title", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->where(['store_id' => $storeProfile->id, 'is_active' => 1])
                    ->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'id' => $item->id,
                                'title' => $item->title,
                                'price' => $item->price,
                                'thumbnail' => $item->getThumbnail(),
                                'category' => $item?->productCategory?->name,
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
}
