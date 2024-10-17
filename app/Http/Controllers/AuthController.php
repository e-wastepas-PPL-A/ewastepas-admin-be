<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Helpers\TokenGenerator;
use App\Http\Requests\Auth\ForgotPasswordUserRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\OtpConfirmationRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordUserRequest;
use App\Models\MemberOtp;
use App\Models\User;
use App\Services\AuthUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    public function register(RegisterUserRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->register($request->name, $request->email, $request->password);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function otpCodeConfirmation(OtpConfirmationRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->otpConfirmation($request->code, $request->email);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function otpCodeForgotPasswordConfirmation(OtpConfirmationRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->otpForgetPasswordConfirmation($request->code, $request->email);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function resendOtpCodeForgotPassword(ResendOtpRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->resendOtpToEmail($request->email, MemberOtp::TYPE_FORGOTPASSWORD);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function resendOtpCode(ResendOtpRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->resendOtpToEmail($request->email);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
        // return ResponseJson::successResponse("Login with google", ['target_url' => $data->getTargetUrl()]);
    }
    /**
     * Method googleCallback
     *
     * @return void
     */
    public function googleCallback()
    {
        $google_user = Socialite::driver('google')->stateless()->user();

        [$proceed, $message, $data] = (new AuthUserService())->googleCallback($google_user);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function login(LoginUserRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->login($request->email, $request->password);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function forgotPassword(ForgotPasswordUserRequest $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->insertEmailForgotPassword($request->email);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function newPassword(ResetPasswordUserRequest $request)
    {

        $payload = [
            'password' => $request->password,
            'reset_pass_token' => $request->reset_pass_token,
        ];
        // reset password 
        [$proceed, $message, $data] = (new AuthUserService())->updatePassword($payload);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function logout()
    {
        $user = request()->user();
        $user->tokens()->delete();

        return ResponseJson::successResponse('Berhasil logout', []);
    }

    public function checkEmailAvailable(Request $request)
    {
        [$proceed, $message, $data] = (new AuthUserService())->checkEmailAvailable($request->email);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}
