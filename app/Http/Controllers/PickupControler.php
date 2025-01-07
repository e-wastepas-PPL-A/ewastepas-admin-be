<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Services\PickupService;
use Illuminate\Http\Request;

class PickupControler extends Controller
{
    public function wastePoint(Request $request)
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        // login
        [$proceed, $message, $data] = (new PickupService())->getWastePoint($limit, $search);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function listPickupUser(Request $request)
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        // login
        [$proceed, $message, $data] = (new PickupService())->listPickupUser($limit, $search);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function listPickupCourier(Request $request)
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        // login
        [$proceed, $message, $data] = (new PickupService())->listPickupCourier($limit, $search);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function penerimaanPenjemputan(Request $request)
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        // login
        [$proceed, $message, $data] = (new PickupService())->penerimaanPenjemputan($limit, $search);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function permintaanPenjemputan(Request $request)
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        // login
        [$proceed, $message, $data] = (new PickupService())->listPickupWasteRequest($limit, $search);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function riwayatPenjemputan(Request $request)
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        // login
        [$proceed, $message, $data] = (new PickupService())->listPickupHistories($limit, $search);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function detailPenerimaanPenjemputan($id)
    {

        // login
        [$proceed, $message, $data] = (new PickupService())->detailPickup($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function detailPermintaanPenjemputan($id)
    {

        // login
        [$proceed, $message, $data] = (new PickupService())->detailPickup($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function detailRiwayatPenjemputan($id)
    {

        // login
        [$proceed, $message, $data] = (new PickupService())->detailPickup($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function detailWastePoint($id)
    {

        // login
        [$proceed, $message, $data] = (new PickupService())->detailWastePoint($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
