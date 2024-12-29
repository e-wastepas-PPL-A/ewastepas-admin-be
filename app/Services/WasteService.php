<?php

namespace App\Services;

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
                                'image' => asset('/storage/uploads/waste_photos/' . basename($item->image)) ?? null,
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


    // Fungsi untuk membuat data Waste baru
    public function createWaste($data)
    {
        try {
            // Mulai transaksi
            DB::beginTransaction();

            // Sanitisasi data
            foreach ($data as $key => $value) {
                // Tidak termasuk sanitasi untuk file gambar
                if ($key !== 'image') {
                    $data[$key] = htmlspecialchars(strip_tags($value));
                }
            }

            // Proses unggah file gambar (jika ada)
            $photoUrl = null;
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $file = $data['image'];
                // Unggah file gambar baru
                $path = $file->store('uploads/waste_photos', 'public');
                // Dapatkan URL gambar
                $photoUrl = Storage::url($path);

                // Perbaiki URL jika ada garis miring ganda
                $photoUrl = preg_replace('/([^:])(\/{2,})/', '$1/', $photoUrl);
            }

            // Buat data Waste baru
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

            // Commit transaksi
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

            // Sanitisasi data
            foreach ($data as $key => $value) {
                // Tidak termasuk sanitasi untuk file gambar
                if ($key !== 'image') {
                    $data[$key] = htmlspecialchars(strip_tags($value));
                }
            }

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
                // Perbaiki URL jika ada garis miring ganda
                $photoUrl = preg_replace('/([^:])(\/{2,})/', '$1/', $photoUrl);
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
            'image' => asset('storage/uploads/waste_photos' . basename($Waste->image)),
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
