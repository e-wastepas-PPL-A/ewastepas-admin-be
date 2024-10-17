<?php

namespace App\Services;

use App\Helpers\LinkyiStorage;
use App\Mail\EmailActivationMemberRegister;
use App\Models\MemberOtp;
use App\Models\Store;
use App\Models\StoreTheme;
use App\Models\StoreVerification;
use App\Models\StoreView;
use App\Models\StoreVisitor;
use App\Models\Theme;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProfileService
{

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
        $otp_expiration_time = 120; //> 2 mnt
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
        $sendOTP = $this->sendOTPCodeToEmail($email, $type);
        if (!$sendOTP) {
            return [false, 'Kode OTP gagal dikirim', []];
        }
        return [true, 'Kode OTP telah dikirim silahkan periksa email anda', []];
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

    public function updateProfile($data): array
    {
        try {
            DB::beginTransaction();
            $user = request()->user();
            $user = User::where(['email' => $user->email])->first();

            $updateProfile = [
                'name'      => $data['name']
            ];
            if ($data['avatar']) {
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }
                $updateProfile['avatar'] = $data['avatar']->store('avatar');
            }
            $updated = $user->update($updateProfile);

            DB::commit();
            if ($updated) {
                return [true, 'Berhasil Memperbarui profil', []];
            } else {
                return [false, 'Gagal Memperbarui profil', []];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return [false, 'Server is busy right now', []];
        }
    }
    public function profileUpdatePassword($data): array
    {
        try {
            DB::beginTransaction();
            $user = request()->user();
            $admin = User::where(['email' => $user->email])->first();

            if (!Hash::check($data['current_password'], $admin->password)) {
                return [false, 'Password tidak sesuai', []];
            }
            $newPassword = bcrypt($data['password']);

            $updated = $admin->update(['password' => $newPassword]);

            DB::commit();
            if ($updated) {
                return [true, 'Berhasil Memperbarui password', []];
            } else {
                return [false, 'Gagal Memperbarui password', []];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return [false, 'Server is busy right now', []];
        }
    }
    public function getProfile()
    {
        try {
            $user = Auth()->user();
            $user = User::where(['email' => $user->email])->first();

            if (!$user) {
                return [false, 'Data tidak ditemukan', []];
            }
            $response = [
                'profile' =>  [
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->storeStatus(),
                    'google_login' => ($user->google_id != null && $user->password == null)
                ],
                'store' => [
                    'name' => $user?->store?->name,
                    'username' => $user?->store?->slug,
                    'link' => $user?->store?->generateLink(),
                    'description' => $user?->store?->description,
                    'logo' => $user?->store?->getLogo(),
                    'verified' => $user?->store?->statusVerification?->status,
                ]
            ];

            return [true, 'Profile', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
    public function checkUsername($username)
    {
        try {
            $checkUser = true;

            //> cek toko dengan username tersebut
            $checkUsername = Store::where(['slug' => $username])->first();
            if ($checkUsername) {
                $checkUser = false;
            }
            $response = [
                'username' =>  str()->slug($username),
                'availability' => $checkUser,
            ];

            return [true, 'Check Username availability', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
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
            'expired' => $code2fa->created_at->addSeconds(120)->translatedFormat('d F Y H:i:s') . ' WIB'
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

    public function updateStoreProfileActivation($data)
    {
        try {
            // check apakah user sudah setup toko 
            $store = Store::where('user_id', Auth()->id())->first();
            if ($store) {
                return [false, 'Toko anda sudah tersedia silahkan lanjutkan', []];
            }
            DB::beginTransaction();
            $user = request()->user();

            $username = str()->slug($data['username']);
            $description = trim($data['description']);

            if ($data['logo']) {

                $logo = LinkyiStorage::uploadStoreProfile($data['logo']);
            }
            //> jika login with google dan passwordnya masih null set password
            if (!$user->password && $user->google_id && $data['password']) {
                User::whereId(auth()->id())->update(['password' => bcrypt($data['password'])]);
            }
            //> create store
            $store = Store::create(['user_id' => $user->id, 'name' => trim($data['name']), 'slug' => $username, 'logo' => $logo, 'description' => $description]);

            $theme = Theme::where('is_active', 1)->first();
            //> crete views
            StoreView::create(['store_id' => $store->id, 'total' => 0]);
            StoreTheme::create(['store_id' => $store->id, 'theme_id' => $theme->id, 'type' => StoreTheme::TYPE_FREE]);
            StoreVerification::create(['store_id' => $store->id, 'status' => StoreVerification::STATUS_UNVERIFIED]);

            DB::commit();
            return [true, 'Berhasil menyiapkan profil toko', []];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return [false, 'Gagal menyiapkan profil toko silahkan coba lagi', []];
        }
    }
    public function getStoreProfile($slug)
    {
        try {
            $storeProfile = Store::where(['slug' => $slug])->first();

            if (!$storeProfile) {
                return [false, 'Halaman tidak ditemukan', []];
            }

            $response = [
                'store' => [
                    'name' => $storeProfile->name,
                    'slug' => $storeProfile->slug,
                    'link' => $storeProfile->generateLink(),
                    'description' => $storeProfile->description,
                    'logo' => $storeProfile->getLogo(),
                    'verified' => $storeProfile?->statusVerification?->status,
                ],
                'theme' => [
                    'name' => $storeProfile->storeTheme->theme->name,
                    'path' => $storeProfile->storeTheme->theme->path,
                ],

                'links' => $storeProfile->bioLinks->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'link' => $item->link,
                        'type' => $item->type,
                    ];
                }),
                'categories' => $storeProfile->productCategories->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'slug' => $item->slug,
                    ];
                })

            ];

            return [true, 'Profile', $response];
        } catch (\Throwable $exception) {
            Log::error($exception);
            return [false, 'Server is busy right now!', []];
        }
    }
}
