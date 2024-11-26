<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\Waste\UpdateWasteConvertRequest;
use App\Services\WasteConvertService;

class WasteConvertController extends Controller
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

        [$proceed, $message, $data] = (new WasteConvertService())->listWasteConvert($limit, $search, $filter);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function update(UpdateWasteRequest $request, $id)
    {
        [$proceed, $message, $data] = (new WasteConvertService())->updateWasteConvert($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new WasteConvertService())->detailWaste($id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
