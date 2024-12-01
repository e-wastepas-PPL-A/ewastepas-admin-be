<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Community;
use App\Models\CommunityPoints;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommunityService
{
    public function listCommunity($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Community::with('CommunityPoints') // Menambahkan relasi CommunityPoints
                    ->when($search, function ($query) use ($search) {
                        return $query->where("name", 'LIKE', "%$search%");
                    })
                    ->where('is_verified', 0)
                    ->when($sort, function ($query) use ($sort) {
                        return $query->orderBy('created_at', $sort);
                    })
                    ->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {
                            return [
                                'Community_id' => $item->community_id,
                                'name' => $item->name,
                                'email' => $item->email,
                                'phone' => $item->phone,
                                'date_of_birth' => $item->date_of_birth,
                                'address' => $item->address,
                                'photo' => $item->photo,
                                'is_verified' => $item->is_verified,
                                'otp_code' => $item->otp_code,
                                'otp_expiry' => $item->otp_expiry,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                                'Community_points' => [
                                    'points_id' => $item->CommunityPoints->points_id ?? null,
                                    'community_id' => $item->CommunityPoints->community_id ?? null,
                                    'total_points' => $item->CommunityPoints->total_points ?? null,
                                    'created_at' => $item->CommunityPoints->created_at ?? null,
                                    'updated_at' => $item->CommunityPoints->updated_at ?? null,
                                ],
                            ];
                        });
                }
            );

            $data->withPath($limit);

            $response = [
                'Community' => $data
            ];
            return [true, 'List Community', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }


    public function createCommunity($data)
    {

        try {
            DB::beginTransaction();
            Community::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'date_of_birth' => $data['date_of_birth'],
                'address' => $data['address'],
                'photo' => $data['photo'],
                'is_verified' => 0,
                'otp_code' => null,
                'otp_expiry' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return [true, 'Berhasil Menambahkan Community', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateCommunity($data, $id)
    {
        try {
            DB::beginTransaction();
            $Community = Community::where(['Community_id' => $id])->first();
            $CommunityPoints = CommunityPoints::where(['Community_id' => $id])->first();
            if (!$Community) {
                return [false, 'Community tidak ditemukan', []];
            }

            $payload = [
                'name' => $data['name'] ?? $Community->name,
                'email' => $data['email'] ?? $Community->email,
                'phone' => $data['phone'] ?? $Community->phone,
                'date_of_birth' => $data['date_of_birth'] ?? $Community->date_of_birth,
                'address' => $data['address'] ?? $Community->address,
                'photo' => $data['photo'] ?? $Community->photo,
                'is_verified' => $data['is_verified'] ?? $Community->is_verified,
                'otp_code' => $data['otp_code'] ?? $Community->otp_code,
                'otp_expiry' => $data['otp_expiry'] ?? $Community->otp_expiry,
                'updated_at' => now()
            ];

            
            //> create produk
            Community::where(['Community_id' => $id])->update($payload);

            // if is_active = 1, update Community points
            if ($Community->is_active == 1) {
                $payloadPoints = [
                    'total_points' => $data['total_points'] ?? $CommunityPoints->total_points,
                    'updated_at' => now()
                ];
                CommunityPoints::where(['Community_id' => $id])->update($payloadPoints);
            }

            DB::commit();
            return [true, 'Berhasil Memperbaharui Community', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateStatusCommunity($data, $id)
    {
        try {
            DB::beginTransaction();
            $Community = Community::where(['community_id' => $id])->first();
            if (!$Community) {
                return [false, 'Community tidak ditemukan', []];
            }

            // Check if community account_number, nik, ktp_url, kk_url, photo, is_verified is empty
            if (empty($Community->name) || empty($Community->email) || empty($Community->phone) || empty($Community->date_of_birth) || empty($Community->address) || empty($Community->photo)) {
                return [false, 'Community belum melengkapi data', []];
            } else if ($Community->is_verified == 1) {
                return [false, 'Community sudah diverifikasi', []];
            }
            
            // Jika data['is_verified'] bukan 0 atau 1 maka return false
            if (!in_array($data['is_verified'], [0, 1])) {
                return [false, 'Verifikasi tidak valid', []];
            }

            $Community->update([
                'is_verified' => $data['is_verified'],
                'updated_at' => now()
            ]);
            
            CommunityPoints::create([
                'community_id' => $Community->community_id,
                'total_points' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return [true, 'Community berhasil diverifikasi', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailCommunity($id)
    {
        $Community = Community::where(['Community_id' => $id])->first();
        $CommunityPoints = CommunityPoints::where(['Community_id' => $id])->first();
        if (!$Community) {
            return [false, 'Community tidak ditemukan', [$id]];
        }

        $response = [
            'Community_id' => $Community->community_id,
            'name' => $Community->name,
            'email' => $Community->email,
            'phone' => $Community->phone,
            'date_of_birth' => $Community->date_of_birth,
            'address' => $Community->address,
            'photo' => $Community->photo,
            'Community_points' => [
                'points_id' => $CommunityPoints->points_id ?? null,
                'Community_id' => $CommunityPoints->community_id ?? null,
                'total_points' => $CommunityPoints->total_points ?? null,
                'created_at' => $CommunityPoints->created_at ?? null,
                'updated_at' => $CommunityPoints->updated_at ?? null,
            ],
            'is_verified' => $Community->is_verified,
            'otp_code' => $Community->otp_code,
            'otp_expiry' => $Community->otp_expiry,
            'created_at' => $Community->created_at,
            'updated_at' => $Community->updated_at,
        ];
        return [true, "Detail Community", $response];
    }

    public function deleteCommunity($id)
    {
        try {
            DB::beginTransaction();
            $Community = Community::where(['Community_id' => $id])->first();
            if (!$Community) {
                return [false, "Community tidak ditemukan", []];
            }
            $Community->delete();
            DB::commit();
            return [true, 'Community berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
}
