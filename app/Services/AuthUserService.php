<?php

namespace App\Services;

use App\Jobs\SendEmailActivationMemberRegister;
use App\Mail\EmailActivationMemberRegister;
use App\Models\Admin;
use App\Models\Management;
use App\Models\MemberOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;

class AuthUserService
{

    public function login($email, $password): array
    {
        try {
            // login

            $userLogin = Management::whereEmail($email)->where('is_admin', 1)->first();

            // if login true
            if ($userLogin) {
                //> check password
                if (Hash::check($password, $userLogin->password)) {

                    $this->deleteTokenSanctum($userLogin->id);

                    $tokenAirlock = $userLogin->createToken('admin', ['accessLoginAdmin']);

                    $response = [
                        'token' => $tokenAirlock->plainTextToken,
                        'is_active' => true
                    ];
                    return [true, 'Login berhasil', $response];
                }
            }
            return [false, 'Email atau Password Tidak Sesuai', []];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now', []];
        }
    }

    public function otpForgetPasswordConfirmation($code, $email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }
        $otp = MemberOtp::where(['email' => $email, 'code' => $code])->first();

        if (!$otp) {
            return [false, 'Kode OTP tidak valid', []];
        }
        $createdAt = Carbon::parse($otp->created_at);
        $currentTime = Carbon::now();
        $timeDifference = $createdAt->diffInSeconds($currentTime);
        $otp_expiration_time = 3600; //> 1 jam
        if ($timeDifference > $otp_expiration_time) {
            // $otp->delete();
            return [false, 'Kode OTP tidak valid', []];
        }
        // $otp->delete();
        return [true, 'Kode OTP Berhasil dikonfirmasi', [
            'code' => $code
        ]];
    }
    public function resendOtpToEmail($email, $type = MemberOtp::TYPE_ACTIVATION)
    {
        $user = User::whereEmail($email)->first();
        if (!$user) {
            return [false, 'Silahkan mendaftar terlebih dahulu', []];
        }
        if ($type == MemberOtp::TYPE_ACTIVATION && $user->status == User::STATUS_VERIFIED) {
            return [false, 'Akun anda sudah aktif', []];
        }
        $sendOTP = $this->sendOTPCodeToEmail($email, $type);
        if (!$sendOTP) {
            return [false, 'Kode OTP gagal dikirim', []];
        }
        return [true, 'Kode OTP telah dikirim silahkan periksa email anda', []];
    }

    public function insertEmailForgotPassword($email)
    {
        try {
            DB::beginTransaction();
            $user = User::whereEmail($email)->whereStatus(User::STATUS_VERIFIED)->first();
            if ($user) {

                $sendOTP = $this->sendOTPCodeToEmail($email, MemberOtp::TYPE_FORGOTPASSWORD);
                if (!$sendOTP) {
                    return [false, 'Kode OTP gagal dikirim', []];
                }

                DB::commit();
                return [true, 'Silahkan konfirmasi Kode OTP untuk melanjutkan', []];
            } else {
                return [false, 'Pastikan email anda terdaftar dan sudah terverifikasi', []];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            Log::warning("Someone with email " . $email . " try to Reset Password with invalid/unregistered email");
            return [false, 'Server is busy right now', []];
        }
    }

    public function updatePassword($data)
    {
        try {
            DB::beginTransaction();
            // check email and token reset password
            $otpCode = MemberOtp::where('code', $data['reset_pass_token'])->first();
            if (!$otpCode) {
                return [false, 'Token invalid', []];
            }

            $newPassword = bcrypt($data['password']);


            $memberUpdatePassword = User::where(['email' => $otpCode->email])->update(['password' => $newPassword]);
            //> logout
            if (Auth()->user()) {
                $user = request()->user();
                $user->tokens()->delete();
            }
            // delete token reset password
            MemberOtp::where(['email' => $otpCode->email])->delete();

            DB::commit();
            if ($memberUpdatePassword) {
                return [true, 'Berhasil Memperbarui Password', []];
            } else {
                return [false, 'Gagal Memperbarui Password', []];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return [false, 'Server is busy right now', []];
        }
    }

    private function deleteTokenSanctum($id)
    {
        PersonalAccessToken::where(['tokenable_id' => $id, 'abilities' => '["accessLoginAdmin"]'])->delete();
    }



    public function sendOTPCodeToEmail($email, $type)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }
        MemberOtp::where(['email' => $email])->delete();
        $code = rand(100000, 999999);
        $code2fa = MemberOtp::create(['email' => $email, 'code' => $code, 'type' => $type]);

        $dataEmail = [
            'email' => $email,
            'code' => $code,
            'name' => $user->name,
            'type' => $type,
            'expired' => $code2fa->created_at->addSeconds(3600)->translatedFormat('d F Y H:i') . ' WIB'
        ];

        if ($type == MemberOtp::TYPE_ACTIVATION) {
            Mail::to($email)->send(new EmailActivationMemberRegister($dataEmail));

            // dispatch(new SendEmailActivationMemberRegister($dataEmail))->onQueue('api');
        } elseif ($type == MemberOtp::TYPE_FORGOTPASSWORD) {
            Mail::to($email)->send(new EmailActivationMemberRegister($dataEmail));

            // dispatch(new SendEmailActivationMemberRegister($dataEmail))->onQueue('api');
        } else {
            return false;
        }

        return true;
    }
}
