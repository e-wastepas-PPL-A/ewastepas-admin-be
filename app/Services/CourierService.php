<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Courier;
use App\Models\CourierPoints;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourierService
{
    public function listCourier($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Courier::with('courierPoints') // Menambahkan relasi courierPoints
                    ->when($search, function ($query) use ($search) {
                        return $query->where("name", 'LIKE', "%$search%");
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
                                'courier_id' => $item->courier_id,
                                'name' => $item->name,
                                'email' => $item->email,
                                'phone' => $item->phone,
                                'date_of_birth' => $item->date_of_birth,
                                'address' => $item->address,
                                'account_number' => $item->account_number,
                                'nik' => $item->nik,
                                'ktp_url' => $item->ktp_url,
                                'kk_url' => $item->kk_url,
                                'photo' => $item->photo,
                                'is_verified' => $item->is_verified,
                                'status' => $item->status,
                                'otp_code' => $item->otp_code,
                                'otp_expiry' => $item->otp_expiry,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                                'courier_points' => [
                                    'points_id' => $item->courierPoints->points_id ?? null,
                                    'courier_id' => $item->courierPoints->courier_id ?? null,
                                    'total_points' => $item->courierPoints->total_points ?? null,
                                    'created_at' => $item->courierPoints->created_at ?? null,
                                    'updated_at' => $item->courierPoints->updated_at ?? null,
                                ],
                            ];
                        });
                }
            );

            $data->withPath($limit);

            $data = $data->filter(function ($item) {
                return $item['status'] == 'Pending' || $item['status'] == 'Reject';
            });

            $response = [
                'Courier' => $data
            ];
            return [true, 'List Courier', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }


    public function createCourier($data)
    {

        try {
            DB::beginTransaction();
            Courier::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'date_of_birth' => $data['date_of_birth'],
                'address' => $data['address'],
                'account_number' => $data['account_number'],
                'nik' => $data['nik'],
                'ktp_url' => $data['ktp_url'],
                'kk_url' => $data['kk_url'],
                'photo' => $data['photo'],
                'is_verified' => 0,
                'is_active' => 0,
                'otp_code' => null,
                'otp_expiry' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return [true, 'Berhasil Menambahkan Courier', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateCourier($data, $id)
    {
        try {
            DB::beginTransaction();
            $Courier = Courier::where(['courier_id' => $id])->first();
            $CourierPoints = CourierPoints::where(['courier_id' => $id])->first();
            if (!$Courier) {
                return [false, 'Courier tidak ditemukan', [$Courier]];
            }

            $payload = [
                'name' => $data['name'] ?? $Courier->name,
                'email' => $data['email'] ?? $Courier->email,
                'phone' => $data['phone'] ?? $Courier->phone,
                'date_of_birth' => $data['date_of_birth'] ?? $Courier->date_of_birth,
                'address' => $data['address'] ?? $Courier->address,
                'account_number' => $data['account_number'] ?? $Courier->account_number,
                'nik' => $data['nik'] ?? $Courier->nik,
                'ktp_url' => $data['ktp_url'] ?? $Courier->ktp_url,
                'kk_url' => $data['kk_url'] ?? $Courier->kk_url,
                'photo' => $data['photo'] ?? $Courier->photo,
                'is_verified' => $data['is_verified'] ?? $Courier->is_verified,
                'status' => $Courier->status,
                'otp_code' => $data['otp_code'] ?? $Courier->otp_code,
                'otp_expiry' => $data['otp_expiry'] ?? $Courier->otp_expiry,
                'updated_at' => now()
            ];

            
            //> create produk
            Courier::where(['courier_id' => $id])->update($payload);

            // if is_active = 1, update courier points
            if ($Courier->is_active == 1) {
                $payloadPoints = [
                    'total_points' => $data['total_points'] ?? $CourierPoints->total_points,
                    'updated_at' => now()
                ];
                CourierPoints::where(['courier_id' => $id])->update($payloadPoints);
            }

            DB::commit();
            return [true, 'Berhasil Memperbaharui Courier', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateStatusCourier($data, $id)
    {
        try {
            DB::beginTransaction();
            $Courier = Courier::where(['courier_id' => $id])->first();
            if (!$Courier) {
                return [false, 'Courier tidak ditemukan', []];
            }

            // Check if courier account_number, nik, ktp_url, kk_url, photo, is_verified is empty
            if (empty($Courier->account_number) || empty($Courier->nik) || empty($Courier->ktp_url) || empty($Courier->kk_url) || empty($Courier->photo) || empty($Courier->is_verified)) {
                return [false, 'Courier belum melengkapi data', []];
            } else if ($Courier->is_verified == 0) {
                return [false, 'Courier belum diverifikasi', []];
            } 

            // Jika data['active'] bukan 'Approve', 'Reject', 'Pending', maka return false
            if (!in_array($data['status'], ['Approved', 'Reject', 'Pending'])) {
                return [false, 'Status tidak valid', []];
            }

            $Courier->update([
                'status' => $data['status'],
                'updated_at' => now()
            ]);
            
            CourierPoints::create([
                'courier_id' => $Courier->courier_id,
                'total_points' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return [true, 'Courier berhasil diubah', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailCourier($id)
    {
        $Courier = Courier::where(['courier_id' => $id])->first();
        $CourierPoints = CourierPoints::where(['courier_id' => $id])->first();
        if (!$Courier) {
            return [false, 'Courier tidak ditemukan', [$id]];
        }

        $response = [
            'courier_id' => $Courier->courier_id,
            'name' => $Courier->name,
            'email' => $Courier->email,
            'phone' => $Courier->phone,
            'date_of_birth' => $Courier->date_of_birth,
            'address' => $Courier->address,
            'account_number' => $Courier->account_number,
            'nik' => $Courier->nik,
            'ktp_url' => $Courier->ktp_url,
            'kk_url' => $Courier->kk_url,
            'photo' => $Courier->photo,
            'courier_points' => [
                'points_id' => $CourierPoints->points_id ?? null,
                'courier_id' => $CourierPoints->courier_id ?? null,
                'total_points' => $CourierPoints->total_points ?? null,
                'created_at' => $CourierPoints->created_at ?? null,
                'updated_at' => $CourierPoints->updated_at ?? null,
            ],
            'is_verified' => $Courier->is_verified,
            'is_active' => $Courier->is_active,
            'otp_code' => $Courier->otp_code,
            'otp_expiry' => $Courier->otp_expiry,
            'created_at' => $Courier->created_at,
            'updated_at' => $Courier->updated_at,
        ];
        return [true, "Detail Courier", $response];
    }

    public function deleteCourier($id)
    {
        try {
            DB::beginTransaction();
            $Courier = Courier::where(['courier_id' => $id])->first();
            if (!$Courier) {
                return [false, "Courier tidak ditemukan", []];
            }
            $Courier->delete();
            DB::commit();
            return [true, 'Courier berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
