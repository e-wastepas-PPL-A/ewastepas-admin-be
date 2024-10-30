<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Sampah;
use App\Models\JenisSampah;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SampahService
{
    public function listSampah($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Sampah::with('jenisSampah') // Mengambil data relasi JenisSampah
                    ->when($search, function ($query) use ($search) {
                        return $query->where("Nama_Sampah", 'LIKE', "%$search%");
                    })
                    ->when($sort, function ($query) use ($sort) {
                        return $query->orderBy('created_at', $sort);
                    })
                    ->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {
                            return [
                                'id_sampah' => $item->id_sampah,
                                'Nama_Sampah' => $item->Nama_Sampah,
                                'Berat_Sampah' => $item->Berat_Sampah,
                                'Point' => $item->Point,
                                'id_jenis' => $item->id_jenis,
                                'id_penjemputan' => $item->id_penjemputan,
                                'jenis_sampah' => [
                                    'id_jenis' => $item->jenisSampah->id ?? null,
                                    'Nama_Jenis' => $item->jenisSampah->Nama_Jenis ?? null,
                                ],
                            ];
                        });
                }
            );

            $data->withPath($limit);

            $response = [
                'sampah' => $data
            ];
            return [true, 'List Sampah', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }


    public function createSampah($data)
    {
        try {
            DB::beginTransaction();
            // get uuid jenis sampah by Nama_JenisSampah
            $jenisSampah = JenisSampah::where('Nama_JenisSampah', $data['Nama_JenisSampah'])->first();

            Sampah::create([
                'id_sampah' => Str::uuid(),
                'Nama_Sampah' => $data['Nama_Sampah'],
                'Berat_Sampah' => $data['Berat_Sampah'],
                'Point' => $data['Point'],
                'id_jenis' => $jenisSampah->id_jenis,
                // 'id_penjemputan' => $data['id_penjemputan'],
                'created_at'  => now()
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan Sampah', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateSampah($data, $id)
    {
        try {
            DB::beginTransaction();
            $sampah = Sampah::where(['id_sampah' => $id])->first();
            if (!$sampah) {
                return [false, 'Sampah tidak ditemukan', []];
            }

            if (isset($data['Nama_JenisSampah'])) {
                $jenisSampah = JenisSampah::where('Nama_JenisSampah', $data['Nama_JenisSampah'])->first();
                $id_jenis = $jenisSampah->id_jenis;
            } else if (isset($data['id_jenis'])) {
                $id_jenis = $data['id_jenis'];
            } else {
                $id_jenis = $sampah->id_jenis;
            }

            $payload = [
                'Nama_sampah' => $data['Nama_sampah'] ?? $sampah->Nama_Sampah,
                'Berat_Sampah' => $data['Berat_Sampah'] ?? $sampah->Berat_Sampah,
                'Point' => $data['Point'] ?? $sampah->Point,
                'id_jenis' => $id_jenis ?? $sampah->id_jenis,
                'id_penjemputan' => $data['id_penjemputan'] ?? $sampah->id_penjemputan,
                'updated_at' => now()
            ];

            //> create produk
            Sampah::where(['id_sampah' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Sampah', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailSampah($id)
    {
        $sampah = Sampah::where(['id_sampah' => $id])->first();
        if (!$sampah) {
            return [false, 'Sampah tidak ditemukan', [$id]];
        }

        $response = [
            'id_sampah' => $sampah->id_sampah,
            'Nama_Sampah' => $sampah->Nama_sampah,
            'Berat_Sampah' => $sampah->Berat_Sampah,
            'Point' => $sampah->Point,
            'id_jenis' => $sampah->id_jenis,
            'id_penjemputan' => $sampah->id_penjemputan,
        ];
        return [true, "Detail Sampah", $response];
    }

    public function deleteSampah($id)
    {
        try {
            DB::beginTransaction();
            $sampah = Sampah::where(['id_sampah' => $id])->first();
            if (!$sampah) {
                return [false, "Sampah tidak ditemukan", []];
            }
            Sampah::where(['id_sampah' => $id])->delete();
            DB::commit();
            return [true, 'Sampah berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
