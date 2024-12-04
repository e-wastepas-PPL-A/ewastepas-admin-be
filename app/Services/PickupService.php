<?php

namespace App\Services;

use App\Models\Dropbox;
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
                    'community_id' => $item->community_id,
                    'name' => $item->community->name, // Access community name from related model
                    'point' => $item->pickup_address,
                    'status' => $item->pickup_status,
                    'pickup_detail' => $item->pickupDetail
                ];
            });

            $data->withPath($limit);
            $dropboxs = Dropbox::get();
            $response = [
                'dropboxs' => $dropboxs,
                'pickups' => $data
            ];
            return [true, 'List pickups', $response];
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
                ->with('courier') // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('courier', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%"); // Search for community name
                    });
                })
                ->paginate($limit);

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
    public function listPickupCourier($limit, $search)
    {
        try {
            // $user = Auth()->user();

            // Start the query builder
            $data = PickupWaste::query()
                ->with('courier') // Ensure that the related 'community' model is loaded
                ->when($search, function ($query) use ($search) {
                    return $query->whereHas('courier', function ($query) use ($search) {
                        $query->where("name", 'LIKE', "%$search%"); // Search for community name
                    });
                })
                ->paginate($limit);

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

    public function detailPickupUser($id)
    {
        $data = PickupWaste::with('community', 'courier', 'pickupDetail.waste')->where(['pickup_id' => $id])->first();
        if (!$data) {
            return [false, 'Data tidak ditemukan', [$id]];
        }

        return [true, "Detail pickups", $data];
    }
    public function detailWastePoint($id)
    {
        $data = PickupWaste::with('community', 'pickupDetail.waste')->where(['pickup_id' => $id])->first();
        if (!$data) {
            return [false, 'Data tidak ditemukan', []];
        }

        return [true, "Detail point", $data];
    }
}
