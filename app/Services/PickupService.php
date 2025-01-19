<?php

namespace App\Services;

use App\Models\Dropbox;
use App\Models\PickupDetail;
use App\Models\PickupWaste;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductView;
use Illuminate\Support\Facades\Log;

class PickupService
{

    public function getWastePoint($limit, $search)
    {
        try {
            // $user = Auth()->user();

            // Start the query builder
            $data = PickupWaste::query()
                ->with('community', 'pickupDetail') // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('community', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%"); // Search for community name
                    });
                })
                ->paginate($limit);
            // Transform the paginated collection
            $data->getCollection()->transform(function ($item) {
                return [
                    'pickup_id' => $item->pickup_id,
                    'name' => $item->community->name, // Access community name from related model
                    'point' => $item->pickupDetail->sum('points'),
                    'date' =>  $item->pickup_date ?? $item->created_at
                ];
            });

            $data->withPath($limit);
            $dropboxs = Dropbox::get();
            $response = [
                'dropboxs' => $dropboxs,
                'pickups' => $data
            ];
            return [true, 'List custommer point', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }
    public function penerimaanPenjemputan($limit, $search)
    {
        try {
            // $user = Auth()->user();

            // Start the query builder
            $data = PickupWaste::query()
                ->with(['courier', 'community', 'pickupDetail']) // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('courier', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%"); // Search for community name
                    });
                })
                ->paginate($limit);
            // Transform the paginated collection
            $data->getCollection()->transform(function ($item) {
                return [
                    'pickup_id' => $item->pickup_id,
                    'customer_name' => $item->community->name,
                    'courier_name' => $item?->courier?->name ?? '-',
                    'courier_phone' => $item?->courier?->phone ?? '-',
                    'total_waste' => $item->pickupDetail->sum('quantity'),
                    'status' => $item->pickup_status,
                    'pickup_address' => $item->pickup_address,
                    'date' => $item->pickup_date ?? $item->created_at
                ];
            });

            $data->withPath($limit);

            $response = [
                'pickups' => $data
            ];
            return [true, 'Riwayat penjemputan', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }
    public function listPickupWasteRequest($limit, $search)
    {
        try {
            // $user = Auth()->user();

            // Start the query builder
            $data = PickupWaste::query()
                ->with(['courier', 'community', 'pickupDetail']) // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('community', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%");
                    });
                })
                ->paginate($limit);
            // Transform the paginated collection
            $data->getCollection()->transform(function ($item) {
                return [
                    'pickup_id' => $item->pickup_id,
                    'customer_name' => $item->community->name,
                    'total_waste' => $item->pickupDetail->sum('quantity'),
                    'status' => $item->pickup_status,
                    'pickup_address' => $item->pickup_address,
                    'date' => $item->pickup_date ?? $item->created_at
                ];
            });

            $data->withPath($limit);

            $response = [
                'pickups' => $data
            ];
            return [true, 'Riwayat penjemputan', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function listPickupHistories($limit, $search)
    {
        try {
            // $user = Auth()->user();

            // Start the query builder
            $data = PickupWaste::query()
                ->with(['courier', 'community', 'pickupDetail']) // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('courier', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%"); // Search for community name
                    });
                })
                ->paginate($limit);
            // Transform the paginated collection
            $data->getCollection()->transform(function ($item) {
                return [
                    'pickup_id' => $item->pickup_id,
                    'customer_name' => $item?->community?->name,
                    'driver_name' => $item?->courier?->name ?? '-',
                    'total_waste' => $item->pickupDetail->sum('quantity'),
                    'status' => $item->pickup_status,
                    'date' => $item->pickup_date ?? $item->created_at

                ];
            });

            $data->withPath($limit);

            $response = [
                'pickups' => $data
            ];
            return [true, 'Riwayat penjemputan', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function listPickupUser($limit, $search)
    {
        try {
            // $user = Auth()->user();

            // Start the query builder
            $data = PickupWaste::query()
                ->with('community') // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('community', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%"); // Search for community name
                    });
                })
                ->paginate($limit);

            // // Transform the paginated collection
            // $data->getCollection()->transform(function ($item) {
            //     return [
            //         'pickup_id' => $item->pickup_id,
            //         'community_id' => $item->community_id,
            //         'name' => $item->community->name, // Access community name from related model
            //         'address' => $item->pickup_address,
            //         'status' => $item->pickup_status
            //     ];
            // });

            $data->withPath($limit);

            $response = [
                'pickups' => $data
            ];
            return [true, 'List pickups', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function detailPickup($id)
    {
        $data = PickupWaste::with('community', 'courier', 'dropbox', 'pickupDetail.waste')->where(['pickup_id' => $id])->first();
        if (!$data) {
            return [false, 'Data tidak ditemukan', [$id]];
        }

        $response = [
            'id' => $data->pickup_id,
            'customer_name' => $data?->community?->name,
            'customer_phone' => $data?->community?->phone,
            'total_waste' => $data?->pickupDetail->sum('quantity'),
            'total_point' => $data?->pickupDetail->sum('points'),
            'courier_name' => $data?->courier?->name,
            'courier_phone' => $data?->courier?->phone,
            'dropbox' => $data?->dropbox?->name,
            'dropbox_address' => $data?->dropbox?->address,
            'reason' => $data->reason,
            'status' => $data->pickup_status,
            'date' => $data->pickup_date ?? $data->created_at,
            'waste' => $data->pickupDetail->map(function ($item) {
                return [
                    'id' => $item->waste->waste_id,
                    'waste_name' => $item->waste->waste_name,
                    'image' => $item->waste->image,
                    'point' => $item->waste->point,
                ];
            })
        ];
        return [true, "Detail pickups", $response];
    }

    public function detailWastePoint($id, $search, $limit)
    {
        $data = PickupWaste::with('community', 'pickupDetail.waste')->where(['pickup_id' => $id])->first();
        if (!$data) {
            return [false, 'Data tidak ditemukan', []];
        }
        $pickupDetails = PickupDetail::query()
            ->where('pickup_id', $data->pickup_id)
            ->when($search, function ($query) use ($search, $id) {
                $query->where("quantity", 'LIKE', "%$search%")->orWhere('pickup_id', $id); // Search for community name
            })
            ->paginate($limit);

        // // Transform the paginated collection
        // $data->getCollection()->transform(function ($item) {
        //     return [
        //         'pickup_id' => $item->pickup_id,
        //         'community_id' => $item->community_id,
        //         'name' => $item->community->name, // Access community name from related model
        //         'address' => $item->pickup_address,
        //         'status' => $item->pickup_status
        //     ];
        // });

        $pickupDetails->withPath($limit);

        $response = [
            'pick_up' => $data->pickup_id,
            'total_point' => $data?->pickupDetail->sum('points'),
            'community' => [
                'name' => $data->community->name,
                'phone' => $data->community->phone,
                'address' => $data->community->address
            ],
            'pickup_wastes' => $pickupDetails, //
        ];
        return [true, "Detail point", $response];
    }
}
