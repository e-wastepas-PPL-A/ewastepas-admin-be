<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\WasteType\CreateWasteTypeRequest;
use App\Http\Requests\WasteType\UpdateWasteTypeRequest;
use App\Services\WasteTypeService;

class WasteTypeController extends Controller
{
    public function index()
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        if (!$filter = request()->filter) {
            $filter = null;
        }

        [$proceed, $message, $data] = (new WasteTypeService())->listWasteType($limit, $search, $filter);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function create(CreateWasteTypeRequest $request)
    {
        [$proceed, $message, $data] = (new WasteTypeService())->createWasteType($request->all());

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function update(UpdateWasteTypeRequest $request, $id)
    {
        [$proceed, $message, $data] = (new WasteTypeService())->updateWasteType($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new WasteTypeService())->detailWasteType($id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function delete($id)
    {
        [$proceed, $message, $data] = (new WasteTypeService())->deleteWasteType($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
