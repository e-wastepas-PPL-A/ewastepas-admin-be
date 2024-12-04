<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Helpers\LinkHelper;
use App\Helpers\LinkyiStorage;
use App\Models\Admin;
use App\Models\Dropbox;
use App\Models\LinkProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DropboxService
{
    public function listDropbox($limit, $search, $filters=[])
    {
        try {
            $user = Auth()->user();
            $sort = $filters['sort'] ?? 'desc';

            // sanitize data
            if (isset($limit)) {
                $limit = htmlspecialchars(strip_tags($limit));
            } else if (isset($search)) {
                $search = htmlspecialchars(strip_tags($search));
            } else if (isset($filters)) {
                $filters = array_map(function ($item) {
                    return htmlspecialchars(strip_tags($item));
                }, $filters);
            }

            $data = tap(
                Dropbox::when($search, function ($query) use ($search) {
                    return $query->where("name", 'LIKE', "%$search%")
                        ->orWhere('address', 'LIKE', "%$search%")
                        ->orWhere('district_address', 'LIKE', "%$search%");
                })->when(isset($filters['status']), function ($query) use ($filters) {
                    return $query->where('status', $filters['status']);
                })->when(isset($filters['capacity']), function ($query) use ($filters) {
                    return $query->where('capacity', '>=', $filters['status']);
                })->when($sort, function ($query) use ($sort) {
                    return $query->orderBy('created_at', $sort);
                })->paginate($limit),
                function ($paginatedInstance) {
                    return $paginatedInstance
                        ->getCollection()
                        ->transform(function ($item) {

                            return [
                                'dropbox_id' => $item->dropbox_id,
                                'name' => $item->name,
                                'address' => $item->address,
                                'district_address' => $item->district_address,
                                'longitude' => $item->longitude,
                                'latitude' => $item->latitude,
                                'capacity' => $item->capacity,
                                'status' => $item->status,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                            ];
                        });
                }
            );


            $data->withPath($limit);

            $response = [
                'dropbox' => $data
            ];
            return [true, 'List Dropbox', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function createDropbox($data)
    {

        try {
            DB::beginTransaction();

            // sanitize data
            $data = array_map(function ($item) {
                return htmlspecialchars(strip_tags($item));
            }, $data);
            
            if (isset($data['status'])) {
                return [false, 'Status tidak dapat ditambahkan', []];
            } else if (!in_array($data['district_address'], ['Bandung Utara', 'Bandung Selatan', 'Bandung Barat', 'Bandung Timur', 'Cimahi', 'Kabupaten Bandung', 'Kabupaten Bandung Barat'])) {
                return [false, 'District Address tidak valid', []];
            }

            Dropbox::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'district_address' => $data['district_address'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'capacity' => $data['capacity'],
                'status' => $data['status'],
                'created_at'  => now(),
                'updated_at'  => now()
            ]);
            DB::commit();
            return [true, 'Berhasil Menambahkan dropbox', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function updateDropbox($data, $id)
    {
        try {
            DB::beginTransaction();

            // sanitize data
            $data = array_map(function ($item) {
                return htmlspecialchars(strip_tags($item));
            }, $data);

            $dropbox = Dropbox::where(['dropbox_id' => $id])->first();
            if (!$dropbox) {
                return [false, 'Dropbox tidak ditemukan', []];
            }

            if (isset($data['status'])) {
                return [false, 'Status tidak dapat diubah', []];
            } else if (isset($data['district_address']) && !in_array($data['district_address'], ['Bandung Utara', 'Bandung Selatan', 'Bandung Barat', 'Bandung Timur', 'Cimahi', 'Kabupaten Bandung', 'Kabupaten Bandung Barat'])) {
                return [false, 'District Address tidak valid', []];
            }

            $payload = [
                'name' => $data['name'] ?? $dropbox->name,
                'address' => $data['address'] ?? $dropbox->address,
                'district_address' => $data['district_address'] ?? $dropbox->district_address,
                'longitude' => $data['longitude'] ?? $dropbox->longitude,
                'latitude' => $data['latitude'] ?? $dropbox->latitude,
                'capacity' => $data['capacity'] ?? $dropbox->capacity,
                'status' => $data['status'] ?? $dropbox->status,
                'updated_at' => now()
            ];

            //> create produk
            Dropbox::where(['dropbox_id' => $id])->update($payload);

            DB::commit();
            return [true, 'Berhasil Memperbaharui dropbox', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function detailDropbox($id)
    {
        // sanitize data
        $id = htmlspecialchars(strip_tags($id));

        $dropbox = Dropbox::where(['dropbox_id' => $id])->first();
        if (!$dropbox) {
            return [false, 'Dropbox tidak ditemukan', [$id]];
        }

        $response = [
            'dropbox_id' => $dropbox->dropbox_id,
            'name' => $dropbox->name,
            'address' => $dropbox->address,
            'district_address' => $dropbox->district_address,
            'longitude' => $dropbox->longitude,
            'latitude' => $dropbox->latitude,
            'capacity' => $dropbox->capacity,
            'status' => $dropbox->status,
            'created_at' => $dropbox->created_at,
            'updated_at' => $dropbox->updated_at,
        ];
        return [true, "Detail dropbox", $response];
    }

    public function deleteDelete($id)
    {
        try {
            DB::beginTransaction();
            // sanitize data
            $id = htmlspecialchars(strip_tags($id));
            $dropbox = Dropbox::where(['dropbox_id' => $id])->first();
            if (!$dropbox) {
                return [false, "Dropbox tidak ditemukan", []];
            }
            Dropbox::where(['dropbox_id' => $id])->delete();
            DB::commit();
            return [true, 'Dropbox berhasil dihapus', []];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }

    public function getAnalytics()
    {
        try {
            $totalDropbox = Dropbox::count();
            $statusCount = Dropbox::select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
            $districtCount = Dropbox::select('district_address', DB::raw('count(*) as count'))->groupBy('district_address')->get();
            $averageCapacity = Dropbox::avg('capacity');

            $response = [
                'total_dropbox' => $totalDropbox,
                'status_count' => $statusCount,
                'district_count' => $districtCount,
                'average_capacity' => $averageCapacity
            ];

            return [true, 'Dropbox Analytics', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function generateReport($startDate, $endDate)
    {
        try {
            // sanitize data
            $startDate = htmlspecialchars(strip_tags($startDate));
            $endDate = htmlspecialchars(strip_tags($endDate));
            $dropboxChanges = Dropbox::whereBetween('updated_at', [$startDate, $endDate])->get();
            $totalChanges = $dropboxChanges->count();
            $statusChanges = $dropboxChanges->groupBy('status')->map(function ($item) {
                return $item->count();
            });

            $response = [
                'total_changes' => $totalChanges,
                'status_changes' => $statusChanges,
                'dropbox_changes' => $dropboxChanges
            ];

            return [true, 'Dropbox Report Generated', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }
}
