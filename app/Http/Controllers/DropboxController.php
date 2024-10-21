<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\CreateAdminRequest;
use App\Http\Requests\Dropbox\CreateDropboxRequest;
use App\Http\Requests\Dropbox\UpdateDropboxRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Requests\UpdateStatusAdminRequest;
use App\Services\AdminService;
use App\Services\DropboxService;

class DropboxController extends Controller
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

        [$proceed, $message, $data] = (new DropboxService())->listDropbox($limit, $search, $filter);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function create(CreateDropboxRequest $request)
    {
        [$proceed, $message, $data] = (new DropboxService())->createDropbox($request->all());

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function update(UpdateDropboxRequest $request, $id)
    {
        [$proceed, $message, $data] = (new DropboxService())->updateDropbox($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new DropboxService())->detailDropbox($id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function delete($id)
    {
        [$proceed, $message, $data] = (new DropboxService())->deleteDelete($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
