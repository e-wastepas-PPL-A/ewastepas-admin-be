<?php

namespace App\Services;

use App\Models\PenjemputanSampah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenjemputanSampahService
{
    public function listPenjemputanWIthPagination($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = PenjemputanSampah::with('user')->when($search, function ($query) use ($search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where("Nama", 'LIKE', "%$search%");
                });
            })->when($sort, function ($query) use ($sort) {
                return $query->orderBy('id', $sort);
            })->paginate($limit);


            $data->withPath($limit);

            $response = [
                'penjemputan_masyarakat' => $data
            ];
            return [true, 'Data penjemputan masyarakat', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
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
}
