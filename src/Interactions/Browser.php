<?php
namespace Bzzix\LaravelLrsPackage\Interactions;

class Browser
{
    /**
     * Get browser and platform information using User Agent string directly.
     * This avoids dependency conflicts with older libraries like Jenssegers\Agent.
     *
     * @return array
     */
    public static function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        // Get Platform
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'Windows';
        } elseif (preg_match('/android/i', $u_agent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad/i', $u_agent)) {
            $platform = 'iOS';
        }

        // Get Browser Name
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (preg_match('/Edge/i', $u_agent)) {
            $bname = 'Microsoft Edge';
            $ub = "Edge";
        } elseif (preg_match('/Trident/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } else {
            $ub = "Unknown";
        }

        // Get Version
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (preg_match_all($pattern, $u_agent, $matches)) {
            $i = count($matches['browser']);
            if ($i != 1) {
                if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                    $version = $matches['version'][0];
                } else {
                    $version = $matches['version'][1];
                }
            } else {
                $version = $matches['version'][0];
            }
        }

        if ($version == null || $version == "") {
            $version = "?";
        }

        return [
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'   => $pattern
        ];
    }
}
