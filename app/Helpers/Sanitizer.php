<?php


namespace App\Helpers;

class Sanitizer
{
    /**
     * String Text Sanitizer
     *
     * @param $text
     * @return string
     */
    public static function sanitizeText($text)
    {
        return trim(htmlentities((string) $text, ENT_QUOTES));
    }

    /**
     * HTML Sanitizer
     *
     * @param $text
     * @return string
     */
    public function sanitizeHTML($content)
    {
        if (!$content) {
            return null;
        }

        if (empty($content)) {
            return null;
        }

        if (!$this->imgValid($content)) {
            return null;
        }

        if (!$this->iframeValid($content)) {
            return null;
        }

        if (!$this->ahref($content)) {
            return null;
        }

        $toReplace = "<p><br></p>";
        $content = $this->leftTrimHTML($content);
        $content = $this->rightTrimHTML($content);
        $content = $this->leftReplaceHTML($content, $toReplace);
        $content = $this->rightReplaceHTML($content, $toReplace);
        $content = $this->midReplaceHTML($content, $toReplace);

        return $content;
    }

    private function imgValid($content)
    {
        $imgValid = true;
        $withOnError = strstr($content, "onerror");
        $withImage = strstr($content, "<img");

        if ($withImage && $withImage != "" && $withOnError && $withOnError != "") {
            $imgValid = false;
        }

        if (
            strstr($withImage, 'alert') || strstr($withImage, 'javascript') || strstr($withImage, 'console') ||
            strstr($withImage, 'script') || strstr($withImage, 'fromCharCode')
        ) {
            $imgValid = false;
        }

        return $imgValid;
    }

    private  function iframeValid($content)
    {
        $imgValid = true;
        $withImage = strstr($content, "<iframe");
        if ($withImage && $withImage != "") {
            $imgValid = false;
        }

        return $imgValid;
    }

    private  function ahref($content)
    {
        $imgValid = true;
        $withImage = strstr($content, 'href="javascript');
        if ($withImage && $withImage != "") {
            $imgValid = false;
        }

        return $imgValid;
    }

    private function leftTrimHTML($content)
    {
        $content = ltrim($content);
        $toReplace = "<p> ";
        $r = strlen($toReplace);
        $check = substr($content, 0, $r);
        if ($check == $toReplace) {
            $content = substr_replace($content, "", 0, $r);
        }
        $content = ltrim($content);
        $toReplace = "</p>";
        $r = strlen($toReplace);
        $check = substr($content, 0, $r);
        if ($check == $toReplace) {
            $content = substr_replace($content, "", 0, $r);
        }

        return $content;
    }

    private function rightTrimHTML($content)
    {
        $content = rtrim($content);
        $toReplace = " </p>";
        $r = strlen($toReplace);
        $j = strlen($content);
        $check = substr($content, $j - $r, $r);
        if ($check == $toReplace) {
            $content = substr_replace($content, "", $j - $r, $r);
        }
        $content = rtrim($content);
        $toReplace = "<p>";
        $r = strlen($toReplace);
        $j = strlen($content);
        $check = substr($content, $j - $r, $r);
        if ($check == $toReplace) {
            $content = substr_replace($content, "", $j - $r, $r);
        }

        return $content;
    }

    private function leftReplaceHTML($content, $toReplace)
    {
        $r = strlen($toReplace);
        $startUnsanitized = true;
        while ($startUnsanitized) {
            $check = substr($content, 0, $r);
            if ($check == $toReplace) {
                $content = substr_replace($content, "", 0, $r);
            } else {
                $startUnsanitized = false;
            }
        }

        return $content;
    }

    private function rightReplaceHTML($content, $toReplace)
    {
        $r = strlen($toReplace);
        $endUnsanitized = true;
        while ($endUnsanitized) {
            $j = strlen($content);
            $check = substr($content, $j - $r, $r);
            if ($check == $toReplace) {
                $content = substr_replace($content, "", $j - $r, $r);
            } else {
                $endUnsanitized = false;
            }
        }

        return $content;
    }

    private function midReplaceHTML($content, $toReplace)
    {
        $r = strlen($toReplace);
        $i = 0;
        $k = strlen($content);
        $found = false;
        while ($i < $k) {
            if ($content[$i] == $toReplace[0]) {
                $check = substr($content, $i, $r);
                if ($check == $toReplace) {
                    $content = substr_replace($content, "", $i, $r);
                    $k = strlen($content);
                    $found = true;
                } else {
                    if ($found) {
                        $contentLeft = substr($content, 0, $i);
                        $contentRight = substr($content, $i, strlen($content));
                        $content = $contentLeft . $toReplace . $contentRight;
                        $found = false;
                    }
                    $i++;
                }
            } else {
                $i++;
            }
        }

        return $content;
    }




    public static function forbiddenShortlink($url)
    {
        $shortlink_domains = [
            'bit.ly',
            'goo.gl',
            'tinyurl.com',
            't.co',
            'ow.ly',
            'is.gd',
            'buff.ly',
            'adf.ly',
            'bit.do',
            'cutt.ly',
            'rb.gy',
            'rebrand.ly',
            'mcaf.ee',
            'su.pr',
            'bc.vc',
            'shorte.st',
            'clk.sh',
            'bl.ink',
            'tr.im',
            'tiny.cc',
            'yourls.org',
            'v.gd',
            'soo.gd',
            's.id',
            'qr.ae',
            't2mio.com',
            'u.to',
            'x.co',
            'lnkd.in',
            'yep.it',
            'b.link',
            'hyperurl.co',
            'po.st',
            'ity.im',
            'chilp.it',
            'clck.ru',
            'tny.im',
            'shorl.com',
            'fllw.me',
            'qr.net',
            'scrnch.me',
            '2.gp',
            'g.remark.com',
            'zpr.io',
            'vzturl.com',
            'clc.la',
            'ity.me',
            'v.gd',
            'lnk.co',
            'hit.my',
            'shorturl.at'
        ];

        // Mendapatkan domain dari URL
        $parsed_url = parse_url($url, PHP_URL_HOST);

        // Jika tidak bisa parse URL, anggap URL tidak valid
        if ($parsed_url === false) {
            return false; // URL tidak valid dianggap mengandung domain shortlink
        }
        return !in_array($parsed_url, $shortlink_domains);
    }
}
