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
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class WasteService
{
    public function listWaste($limit, $search, $filter)
    {
        try {
            $user = Auth()->user();
            $sort = isset($filters['sort']) ? $filters['sort'] : 'asc';

            // sanitize data
            if (isset($limit)) {
                $limit = htmlspecialchars(strip_tags($limit));
            } else if (isset($search)) {
                $search = htmlspecialchars(strip_tags($search));
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

            // sanitize data
            $data = array_map(function ($item) {
                return htmlspecialchars(strip_tags($item));
            }, $data);

            // Proses unggah file gambar (jika ada)
            $photoUrl = null;
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $file = $data['image'];
                $path = $file->store('uploads/waste_photos', 'public');
                $photoUrl = Storage::url($path); // Dapatkan URL gambar
            }

            Waste::create([
                'waste_name' => $data['waste_name'],
                'point' => 0,
                'image' => $photoUrl,
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

            // sanitize data
            $data = array_map(function ($item) {
                return htmlspecialchars(strip_tags($item));
            }, $data);

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

            // Proses file gambar jika ada
            $imagePath = $Waste->image; // Default ke gambar lama
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $file = $data['image'];
                if (is_array($file) && count($file) > 1) {
                    return [false, 'Hanya dapat mengunggah satu gambar!', []];
                }

                // Unggah file gambar baru
                $path = $file->store('uploads/waste_photos', 'public');
                $photoUrl = Storage::url($path); // Dapatkan URL gambar
            }

            $payload = [
                'waste_name' => $data['waste_name'] ?? $Waste->waste_name,
                'point' => $Waste->point,
                'image' => $photoUrl ?? $imagePath,
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
        $id = htmlspecialchars(strip_tags($id));

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
            $id = htmlspecialchars(strip_tags($id));
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
