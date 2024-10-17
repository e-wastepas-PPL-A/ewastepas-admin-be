<?php

namespace App\Helpers;

class LinkHelper
{
    public static function getDomainName($url)
    {
        // Parse the URL and extract the host part
        $parsedUrl = parse_url($url, PHP_URL_HOST);

        // If the URL is not valid, return null
        if (!$parsedUrl) {
            return null;
        }

        // Remove 'www.' if it exists
        $host = preg_replace('/^www\./', '', $parsedUrl);

        // Split the host by dots
        $parts = explode('.', $host);

        // Check if we have at least two parts (domain and TLD)
        if (count($parts) < 2) {
            return 'website';
        }

        // Extract the domain name (usually the second to last part)
        return $parts[count($parts) - 2];
    }

    public static function extractUsernameFromUrl($url, $platform)
    {
        $patterns = [
            'facebook' => '/facebook\.com\/([a-zA-Z0-9(\.\?)?]+)/',
            'instagram' => '/instagram\.com\/([a-zA-Z0-9(_\.)?]+)/',
            'tiktok' => '/tiktok\.com\/@([a-zA-Z0-9(\.\?)?]+)/',
            'youtube' => '/youtube\.com\/(channel|c|user)\/([a-zA-Z0-9(\.\?)?]+)/',
            'twitter' => '/twitter\.com\/([a-zA-Z0-9(\.\?)?]+)/',
            'telegram' => '/t\.me\/([a-zA-Z0-9(\.\?)?]+)/',
            'line' => '/line\.me\/R\/ti\/p\/([a-zA-Z0-9(\.\?)?]+)/',
            'likee' => '/likee\.video\/@([a-zA-Z0-9(\.\?)?]+)/',
            'snackvideo' => '/snackvideo\.com\/@([a-zA-Z0-9(\.\?)?]+)/',
        ];

        if (isset($patterns[$platform])) {
            if (preg_match($patterns[$platform], $url, $matches)) {
                $username = $matches[1];

                // Khusus untuk WhatsApp, konversi nomor ke format internasional jika perlu
                if ($platform === 'whatsapp') {
                    $username = self::formatPhoneNumber($username);
                }

                return $username;
            }
        }

        return null;
    }
    public static function formatPhoneNumber($number)
    {
        // Hapus awalan '0' jika ada, dan tambahkan '62' di depannya
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }

        // Jika nomor sudah dalam format internasional, tidak perlu mengubah
        if (substr($number, 0, 2) === '62') {
            return $number;
        }

        return '62' . $number;
    }
}
