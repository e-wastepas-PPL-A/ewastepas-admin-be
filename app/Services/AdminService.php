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
                // 'id' => LinkHelper::generateId(),
                'name'  => $data['name'],
                'email'  => $data['email'],
                'password'  => Hash::make($data['password']),
                'status' => $data['status']
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan admin', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateAdmin($data, $id)
    {
        try {
            DB::beginTransaction();
            $admin = Admin::where(['id' => $id])->first();
            if (!$admin) {
                return [false, 'Admin tidak ditemukan', []];
            }

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (isset($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            //> create produk
            $admin->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Admin', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateStatusAdmin($data, $id)
    {
        try {
            DB::beginTransaction();
            $admin = Admin::where(['id' => $id])->first();
            if (!$admin) {
                return [false, 'Admin tidak ditemukan', []];
            }

            if ($data['is_active'] == 'active') {
                $message = "diaktifkan";
            } else {
                $message = "dinonaktifkan";
            }

            //> create produk
            $admin->update([
                'status' => $data['is_active'],
            ]);

            DB::commit();
            return [true, 'Admin berhasil ' . $message, []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailAdmin($id)
    {
        $admin = Admin::where(['id' => $id])->first();
        if (!$admin) {
            return [false, 'Admin tidak ditemukan', [$id]];
        }

        $response = [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
        ];
        return [true, "Detail admin", $response];
    }

    public function deleteAdmin($id)
    {
        try {
            DB::beginTransaction();
            $admin = Admin::where(['id' => $id])->first();
            if (!$admin) {
                return [false, "Admin tidak ditemukan", []];
            }
            $admin->delete();
            DB::commit();
            return [true, 'Admin berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
