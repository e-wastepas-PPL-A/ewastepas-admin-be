<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Admin;
use App\Models\User;
use App\Models\LinkProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KurirService
{
    public function listKurir($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                User::when($search, function ($query) use ($search) {
                    return $query->where("Nama", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('Created_at', $sort);
                })->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'Nama' => $item->Nama,
                                'Email' => $item->Email,
                                'No_Telp' => $item->No_Telp,
                                'Tgl_Lahir' => $item->Tgl_Lahir,
                                'Alamat' => $item->Alamat,
                                'NIK' => $item->NIK,
                                'No_Rek' => $item->No_Rek,
                                'KTP_URL' => $item->KTP_URL,
                                'KK_URL' => $item->KK_URL,
                                'Foto' => $item->Foto,
                                'Roles' => $item->Roles,
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'Kurir' => $data
            ];
            return [true, 'List Kurir', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createKurir($data)
    {

        try {
            DB::beginTransaction();
            User::create([
                'id_user' => Str::uuid(),
                'Nama' => $data['Nama'],
                'Email' => $data['Email'],
                'No_Telp' => $data['No_Telp'],
                'Tgl_Lahir' => $data['Tgl_Lahir'],
                'Alamat' => $data['Alamat'],
                'NIK' => $data['NIK'],
                'No_Rek' => $data['No_Rek'],
                'KTP_URL' => $data['KTP_URL'],
                'KK_URL' => $data['KK_URL'],
                'Foto' => $data['Foto'],
                'Roles' => $data['Roles'],
                'Created_at'  => now()
            ]);

            DB::commit();
            return [true, 'Berhasil Menambahkan Kurir', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateKurir($data, $id)
    {
        try {
            DB::beginTransaction();
            $kurir = User::where(['id_user' => $id])->first();
            if (!$kurir) {
                return [false, 'Kurir tidak ditemukan', []];
            }

            $payload = [
                'Nama' => $data['Nama'] ?? $kurir->Nama,
                'Email' => $data['Email'] ?? $kurir->Email,
                'No_Telp' => $data['No_Telp'] ?? $kurir->No_Telp,
                'Tgl_Lahir' => $data['Tgl_Lahir'] ?? $kurir->Tgl_Lahir,
                'Alamat' => $data['Alamat'] ?? $kurir->Alamat,
                'NIK' => $data['NIK'] ?? $kurir->NIK,
                'No_Rek' => $data['No_Rek'] ?? $kurir->No_Rek,
                'KTP_URL' => $data['KTP_URL'] ?? $kurir->KTP_URL,
                'KK_URL' => $data['KK_URL'] ?? $kurir->KK_URL,
                'Foto' => $data['Foto'] ?? $kurir->Foto,
                'Roles' => $data['Roles'] ?? $kurir->Roles,
                'Updated_at' => now()
            ];

            //> create produk
            User::where(['id_user' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Kurir', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateStatusKurir($data, $id)
    {
        try {
            DB::beginTransaction();
            $kurir = User::where(['id_user' => $id])->first();
            if (!$kurir) {
                return [false, 'Kurir tidak ditemukan', []];
            }

            if ($kurir->No_Rek == null || $kurir->KTP_URL == null || $kurir->KK_URL == null || $kurir->Foto == null || $kurir->NIK == null) {
                return [false, 'Data kurir belum lengkap', []];
            } else if ($kurir->Roles == 'kurir') {
                return [false, 'Kurir sudah aktif', []];
            }

            $kurir->update([
                'Roles' => $data['Roles'],
            ]);

            DB::commit();
            return [true, 'Kurir berhasil diaktifkan', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailKurir($id)
    {
        $kurir = User::where(['id_user' => $id])->first();
        if (!$kurir) {
            return [false, 'Kurir tidak ditemukan', [$id]];
        }

        $response = [
            'Nama' => $item->nama,
            'Email' => $item->email,
            'No_Telp' => $item->no_telp,
            'Tgl_Lahir' => $item->tgl_lahir,
            'Alamat' => $item->alamat,
            'NIK' => $item->nik,
            'No_Rek' => $item->no_rek,
            'KTP_URL' => $item->ktp_url,
            'KK_URL' => $item->kk_url,
            'Foto' => $item->foto,
            'Roles' => $item->roles,
        ];
        return [true, "Detail kurir", $response];
    }

    public function deleteKurir($id)
    {
        try {
            DB::beginTransaction();
            $kurir = User::where(['id_user' => $id])->first();
            if (!$kurir) {
                return [false, "Kurir tidak ditemukan", []];
            }
            $kurir->delete();
            DB::commit();
            return [true, 'Kurir berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
