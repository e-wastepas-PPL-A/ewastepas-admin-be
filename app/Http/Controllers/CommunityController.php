<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Models\Community;
use App\Http\Requests\Community\CreateCommunityRequest;
use App\Http\Requests\Community\UpdateCommunityRequest;
use App\Http\Requests\Community\UpdateStatusCommunityRequest;
use App\Services\CommunityService;
use Illuminate\Http\Request; // Mengimpor Request
use Illuminate\Support\Facades\Mail; // Pastikan mengimpor Mail
use App\Mail\OtpMail; // Pastikan mengimpor kelas OtpMail

class CommunityController extends Controller
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

        [$proceed, $message, $data] = (new CommunityService())->listCommunity($limit, $search, $filter);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function create(CreateCommunityRequest $request)
    {
        [$proceed, $message, $data] = (new CommunityService())->createCommunity($request->all());

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function update(UpdateCommunityRequest $request, $id)
    {
        [$proceed, $message, $data] = (new CommunityService())->updateCommunity($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function updateStatus(UpdateStatusCommunityRequest $request, $id)
    {
        [$proceed, $message, $data] = (new CommunityService())->updateStatusCommunity($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new CommunityService())->detailCommunity($id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function delete($id)
    {
        [$proceed, $message, $data] = (new CommunityService())->deleteCommunity($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    // Harus atur Mail di env
    public function sendOtp(Request $request)
    {
        $Community = Community::where('email', $request->email)->first();

        if (!$Community) {
            return ResponseJson::failedResponse('Email not found', []);
        }

        $Community->generateOtp();

        // send email
        Mail::to($Community->email)->send(new OtpMail($Community->otp));

        return ResponseJson::successResponse('Email send or found', $Community);
    }

    public function verifyOtp(Request $request)
    {
        $Community = Community::where('email', $request->email)->first();

        if (!$Community) {
            return ResponseJson::failedResponse('Email not found', null);
        }

        if ($Community->verifyOtp($request->otp_code)) 
        {
            $Community->is_verified = true;
            $Community->update([
                'otp_code' => null,
                'otp_expiry' => null,
                'is_verified' => 1, // Assuming you want to verify the community
            ]);
            return ResponseJson::successResponse('Otp verified', $Community);
        }
        
        return ResponseJson::failedResponse('Otp not verified', null);
    }
}
