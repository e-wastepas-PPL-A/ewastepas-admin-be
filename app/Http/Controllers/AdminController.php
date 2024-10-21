<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\CreateAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Requests\UpdateStatusAdminRequest;
use App\Services\AdminService;

class AdminController extends Controller
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

        [$proceed, $message, $data] = (new AdminService())->listAdmin($limit, $search, $filter);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function create(CreateAdminRequest $request)
    {
        [$proceed, $message, $data] = (new AdminService())->createAdmin($request->all());

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function update(UpdateAdminRequest $request, $id)
    {
        [$proceed, $message, $data] = (new AdminService())->updateAdmin($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function updateStatus(UpdateStatusAdminRequest $request, $id)
    {
        [$proceed, $message, $data] = (new AdminService())->updateStatusAdmin($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new AdminService())->detailAdmin($id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function delete($id)
    {
        [$proceed, $message, $data] = (new AdminService())->deleteAdmin($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
