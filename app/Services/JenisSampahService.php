<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\JenisSampah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class JenisSampahService
{
    public function listJenisSampah($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                JenisSampah::when($search, function ($query) use ($search) {
                    return $query->where("jenis_sampah", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'id_jenis' => $item->id_jenis,
                                'jenis_sampah' => $item->jenis_sampah,
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'jenis_sampah' => $data
            ];
            return [true, 'List Jenis Sampah', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createJenisSampah($data)
    {

        try {
            DB::beginTransaction();
            JenisSampah::create([
                // 'id' => LinkHelper::generateId(),
                'jenis_sampah' => $data['jenis_sampah'],
                'created_at' => now(),
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan Jenis Sampah', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateJenisSampah($data, $id)
    {
        try {
            DB::beginTransaction();
            $jenis_sampah = JenisSampah::where(['id_jenis' => $id])->first();
            if (!$jenis_sampah) {
                return [false, 'Jenis Sampah tidak ditemukan', []];
            }

            $payload = [
                'jenis_sampah' => $data['jenis_sampah'],
            ];

            //> create produk
            JenisSampah::where(['id_jenis' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Jenis Sampah', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailJenisSampah($id)
    {
        $sampah = JenisSampah::where(['id_jenis' => $id])->first();
        if (!$sampah) {
            return [false, 'Sampah tidak ditemukan', [$id]];
        }

        $response = [
            'jenis_sampah' => $sampah
        ];
        return [true, "Detail Jenis Sampah", $response];
    }

    public function deleteJenisSampah($id)
    {
        try {
            DB::beginTransaction();
            $sampah = JenisSampah::where(['id_jenis' => $id])->first();
            if (!$sampah) {
                return [false, "Jenis Sampah tidak ditemukan", []];
            }
            JenisSampah::where(['id_jenis' => $id])->delete();
            DB::commit();
            return [true, 'Jenis Sampah berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
