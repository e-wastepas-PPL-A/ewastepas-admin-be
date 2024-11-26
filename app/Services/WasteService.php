<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Waste;
use App\Models\WasteType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class WasteService
{
    public function listWaste($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                Waste::with('wasteType') // Mengambil data relasi WasteType
                    ->when($search, function ($query) use ($search) {
                        return $query->where("waste_name", 'LIKE', "%$search%");
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
                                'waste_id' => $item->waste_id,
                                'waste_name' => $item->waste_name,
                                'point' => $item->point,
                                'image' => $item->image,
                                'description' => $item->description,
                                'waste_type_id' => $item->waste_type_id,
                                'pickup_id' => $item->pickup_id,
                                'waste_type' => [
                                    'waste_type_id' => $item->waste_type->waste_type_id ?? null,
                                    'waste_type_name' => $item->waste_type->waste_type_name ?? null,
                                ],
                            ];
                        });
                }
            );

            $data->withPath($limit);

            $response = [
                'waste' => $data
            ];
            return [true, 'List Waste', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }


    public function createWaste($data)
    {
        try {
            DB::beginTransaction();
            // get uuid Type Waste by Nama_WasteType
            // $WasteType = WasteType::where('waste_type_name', $data['waste_type_name'])->first();

            // if (!$WasteType) {
            //     return [false, 'Waste Type tidak ditemukan', []];
            // }

            Waste::create([
                'waste_name' => $data['waste_name'],
                'point' => 0,
                'image' => $data['image'],
                'description' => $data['description'],
                'waste_type_id' => $data['waste_type_id'],
                'pickup_id' => $data['pickup_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan Waste', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateWaste($data, $id)
    {
        try {
            DB::beginTransaction();
            $Waste = Waste::where(['waste_id' => $id])->first();
            if (!$Waste) {
                return [false, 'Waste tidak ditemukan', []];
            }

            if (isset($data['waste_type_name'])) {
                $WasteType = WasteType::where('waste_type_name', $data['waste_type_name'])->first();
                $waste_type_id = $WasteType->waste_type_id;
            } else if (isset($data['waste_type_id'])) {
                $waste_type_id = $data['waste_type_id'];
            } else {
                $waste_type_id = $Waste->waste_type_id;
            }

            $payload = [
                'waste_name' => $data['waste_name'] ?? $Waste->waste_name,
                'point' => $Waste->point,
                'image' => $data['image'] ?? $Waste->image,
                'description' => $data['description'] ?? $Waste->description,
                'waste_type_id' => $waste_type_id,
                // 'pickup_id' => $data['pickup_id'] ?? $Waste->pickup_id,
                'updated_at' => now()
            ];

            //> create produk
            Waste::where(['waste_id' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Waste', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailWaste($id)
    {
        $Waste = Waste::where(['waste_id' => $id])->first();
        if (!$Waste) {
            return [false, 'Waste tidak ditemukan', [$id]];
        }

        $response = [
            'waste_id' => $Waste->waste_id,
            'waste_name' => $Waste->waste_name,
            'point' => $Waste->point,
            'image' => $Waste->image,
            'description' => $Waste->description,
            'waste_type_id' => $Waste->waste_type_id,
            'pickup_id' => $Waste->pickup_id,
            'waste_type' => [
                'waste_type_id' => $Waste->waste_type->waste_type_id ?? null,
                'waste_type_name' => $Waste->waste_type->waste_type_name ?? null,
            ],
        ];
        return [true, "Detail Waste", $response];
    }

    public function deleteWaste($id)
    {
        try {
            DB::beginTransaction();
            $Waste = Waste::where(['waste_id' => $id])->first();
            if (!$Waste) {
                return [false, "Waste tidak ditemukan", []];
            }
            Waste::where(['waste_id' => $id])->delete();
            DB::commit();
            return [true, 'Waste berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
