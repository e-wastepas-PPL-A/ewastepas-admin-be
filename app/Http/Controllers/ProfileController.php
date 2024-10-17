<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\Profile\StoreProfileActivationRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profile()
    {
        [$proceed, $message, $data] = (new ProfileService())->getProfile();
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function storeProfileActivation(StoreProfileActivationRequest $request)
    {
        [$proceed, $message, $data] = (new ProfileService())->updateStoreProfileActivation($request->all());
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function checkUsernameAvailability(Request $request)
    {
        [$proceed, $message, $data] = (new ProfileService())->checkUsername($request->username);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $payload = [
            'password' => $request->password,
            'confirm_password' => $request->confirm_password,
            'current_password' => $request->current_password
        ];
        [$proceed, $message, $data] = (new ProfileService())->profileUpdatePassword($payload);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
