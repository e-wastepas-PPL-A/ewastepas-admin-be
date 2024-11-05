<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\WasteType;
use App\Models\Waste;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WasteTypeService
{
    public function listWasteType($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = 'desc';

            if ($filter == 'asc') {
                $sort = 'asc';
            }

            $data = tap(
                WasteType::when($search, function ($query) use ($search) {
                    return $query->where("waste_type_name", 'LIKE', "%$search%");
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {
                            return [
                                'waste_type_id' => $item->waste_type_id,
                                'waste_type_name' => $item->waste_type_name,
                                'image' => $item->image,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'waste_type' => $data
            ];
            return [true, 'List Waste Type', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createWasteType($data)
    {

        try {
            DB::beginTransaction();
            WasteType::create([
                'waste_type_name' => $data['waste_type_name'],
                'image' => $data['image'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan Waste Type', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateWasteType($data, $id)
    {
        try {
            DB::beginTransaction();
            $jenis_Waste = WasteType::where(['waste_type_id' => $id])->first();
            if (!$jenis_Waste) {
                return [false, 'Waste Type tidak ditemukan', []];
            }

            $payload = [
                'waste_type_name' => $data['waste_type_name'] ?? $jenis_Waste->waste_type_name,
                'image' => $data['image'] ?? $jenis_Waste->image,
                'updated_at' => now(),
            ];

            //> create produk
            WasteType::where(['waste_type_id' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui Waste Type', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailWasteType($id)
    {
        $Waste = WasteType::where(['waste_type_id' => $id])->first();
        if (!$Waste) {
            return [false, 'Waste Type tidak ditemukan', [$id]];
        }

        $response = [
            'waste_type_id' => $Waste->waste_type_id,
            'waste_type_name' => $Waste->waste_type_name,
            'image' => $Waste->image,
            'created_at' => $Waste->created_at,
            'updated_at' => $Waste->updated_at,
        ];
        return [true, "Detail Waste Type", $response];
    }

    public function deleteWasteType($id)
    {
        try {
            DB::beginTransaction();
            $Waste = Waste::where(['waste_type_id' => $id])->first();

            if ($Waste) {
                Waste::where(['waste_type_id' => $id])->update(['waste_type_id' => null]);
            }

            $WasteType = WasteType::where(['waste_type_id' => $id])->first();
            if (!$WasteType) {
                return [false, "Waste Type tidak ditemukan", []];
            }
            WasteType::where(['waste_type_id' => $id])->delete();
            DB::commit();
            return [true, 'Waste Type berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

}
