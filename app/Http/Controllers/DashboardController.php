<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // login
        [$proceed, $message, $data] = (new DashboardService())->getDashboardSummary();
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
