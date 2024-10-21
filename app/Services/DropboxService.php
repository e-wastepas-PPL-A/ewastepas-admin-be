<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Admin;
use App\Models\Dropbox;
use App\Models\LinkProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DropboxService
{
    public function listDropbox($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Dropbox::when($search, function ($query) use ($search) {
                    return $query->where("LocationName", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'DropboxID' => $item->DropboxID,
                                'LocationName' => $item->LocationName,
                                'Address' => $item->Address,
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'products' => $data
            ];
            return [true, 'List Dropbox', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createDropbox($data)
    {

        try {
            DB::beginTransaction();
            Dropbox::create([
                // 'id' => LinkHelper::generateId(),
                'LocationName'  => $data['LocationName'],
                'Address'  => $data['Address'],
                'created_at'  => now()
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan dropbox', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateDropbox($data, $id)
    {
        try {
            DB::beginTransaction();
            $dropbox = Dropbox::where(['DropboxID' => $id])->first();
            if (!$dropbox) {
                return [false, 'Dropbox tidak ditemukan', []];
            }

            $payload = [
                'LocationName' => $data['LocationName'],
                'Address' => $data['Address'],
                'updated_at' => now()
            ];

            //> create produk
            Dropbox::where(['DropboxID' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui dropbox', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailDropbox($id)
    {
        $dropbox = Dropbox::where(['DropboxID' => $id])->first();
        if (!$dropbox) {
            return [false, 'Dropbox tidak ditemukan', [$id]];
        }

        $response = [
            'DropboxID' => $dropbox->DropboxID,
            'LocationName' => $dropbox->LocationName,
            'Address' => $dropbox->Address,
            'created_at' => $dropbox->created_at,
        ];
        return [true, "Detail dropbox", $response];
    }

    public function deleteDelete($id)
    {
        try {
            DB::beginTransaction();
            $dropbox = Dropbox::where(['DropboxID' => $id])->first();
            if (!$dropbox) {
                return [false, "Dropbox tidak ditemukan", []];
            }
            Dropbox::where(['DropboxID' => $id])->delete();
            DB::commit();
            return [true, 'Dropbox berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
