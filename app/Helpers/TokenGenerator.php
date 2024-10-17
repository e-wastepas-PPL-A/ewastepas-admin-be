<?php

namespace App\Helpers;

use App\Models\SoftTokenLifetime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class TokenGenerator
{

    const TOKEN_PREFIX = "LINKYI_";
    public static function generateToken($prefix)
    {
        $finalPrefix = self::TOKEN_PREFIX . $prefix;
        $generate = Hash::make($finalPrefix);

        return $generate;
    }


    public static function createToken($type)
    {

        $token = self::generateToken($type);
        SoftTokenLifetime::create(['token' => $token, 'type' => $type]);
        return $token;
    }

    public static function validateToken($token, $type)
    {
        $softTokenLifeTime = SoftTokenLifetime::where(['token' => $token, 'type' => $type])->first();
        if (!$softTokenLifeTime) {
            return false;
        }
        $finalToken = self::TOKEN_PREFIX . $type;

        if (!Hash::check($finalToken, $token)) {
            return false;
        }
        $dateTimeNow = Carbon::now();
        $diffInMinute = $softTokenLifeTime->created_at->diff($dateTimeNow);
        if ($diffInMinute->i > 3) {
            $softTokenLifeTime->delete();
            return false;
        }

        $softTokenLifeTime->delete();
        return true;
    }
}
