<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Services\PenjemputanSampahService;
use Illuminate\Http\Request;

class PenjemputanSampahController extends Controller
{
    public function index(Request $request)
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
        // login
        [$proceed, $message, $data] = (new PenjemputanSampahService())->listPenjemputanWIthPagination($limit, $search, $filter);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
