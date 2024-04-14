<?php

use PHPvian\Views\View;

if (!function_exists('md5_gen')) {
    /**
     * Generates random characters using MD5 values
     * 32 Characters
     */
    function md5_gen()
    {
        return md5(uniqid() . time() . microtime());
    }
}

if (!function_exists('sha1_gen')) {
    /**
     * Generates random characters using SHA1 values
     * 40 Characters
     */
    function sha1_gen($data = null)
    {
        return sha1(uniqid() . time() . microtime() . md5_gen() . $data);
    }
}

if (!function_exists('config')) {
    /**
     * Load config folder files
     */
    function config($file, $key = null)
    {
        $folder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $file . '.php';
        if (file_exists($folder)) {
            $config = require $folder;

            if ($key !== null) {
                if (array_key_exists($key, $config)) {
                    return $config[$key];
                } else {
                    throw new Exception("Key '$key' not found in configuration file '$file'.");
                }
            } else {
                return $config;
            }
        } else {
            throw new Exception("Configuration file '<b>$file</b>' not found.");
        }
    }
}

if (!function_exists('base_url')) {
    /**
     * Function to access base url of project
     */
    function base_url($portOnly = false)
    {
        if ($portOnly) {
            return $_SERVER['SERVER_PORT'];
        }
        $port = isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '';
        return $_SERVER['SERVER_NAME'] . $port;
    }
}

if (!function_exists('view')) {
    /**
     * Function to include a view
     */
    function view($file, $data = [])
    {
        return (new View())->load($file, $data);
    }
}

if (!function_exists('valid_ip')) {
    function valid_ip($ip)
    {
        if (strtolower($ip) === "unknown") {
            return false;
        }

        // Check if the IP is valid using filter_var
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // List of invalid IP ranges
        $invalid_ranges = [
            '0.0.0.0/8',        // Intervalo privado
            '10.0.0.0/8',       // Intervalo privado
            '127.0.0.0/8',      // Intervalo privado
            '169.254.0.0/16',   // Intervalo privado
            '172.16.0.0/12',    // Intervalo privado
            '192.0.0.0/24',     // Intervalo privado
            '192.168.0.0/16',   // Intervalo privado
            '224.0.0.0/4'       // Multicast
        ];

        // Checks if the IP is in one of the invalid ranges
        foreach ($invalid_ranges as $range) {
            list($subnet, $mask) = explode('/', $range);
            if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('get_ip')) {
    function get_ip()
    {
        $headers_to_check = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers_to_check as $header) {
            if (!empty($_SERVER[$header])) {
                // If there are multiple IPs, use the first valid IP
                $ips = explode(',', $_SERVER[$header]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (valid_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }

        // If no valid IP is found, return REMOTE_ADDR (last resort)
        return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
    }
}

if (!function_exists('is_valid_int')) {
    function is_valid_int($value)
    {
        return is_numeric($value) && $value > 0 && $value == intval($value);
    }

}

if (!function_exists('md5_gen')) {
    function md5_gen()
    {
        return md5(uniqid() . time() . microtime());
    }
}

// Function to check the existence of data in the request
if (!function_exists('input_exists')) {
    function input_exists($type = "POST")
    {
        switch ($type) {
            case "POST":
                return (!empty($_POST)) ? true : false;
            case "GET":
                return (!empty($_GET)) ? true : false;
            default:
                return false;
        }
    }
}

// Function to obtain a specific value from the request
if (!function_exists('input')) {
    function input($value)
    {
        if (isset($_POST[$value])) {
            return trim(strip_tags(filter_input(INPUT_POST, $value)));
        } elseif (isset($_GET[$value])) {
            return trim(strip_tags(filter_input(INPUT_GET, $value)));
        }
        return "";
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302)
    {
        // Validate the URL
        $url = filter_var($url, FILTER_VALIDATE_URL);

        if ($url === false) {
            // Invalid URL, redirect to an error page or home page
            $url = "/";
        }

        // Redirect using HTTP header if possible
        if (!headers_sent()) {
            header("Location: " . $url, true, $statusCode);
            exit();
        } else {
            // Use JavaScript for redirection if headers have already been sent
            echo '<script type="text/javascript">';
            echo 'window.location.href="' . $url . '";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
            echo '</noscript>';
            exit();
        }
    }
}

if (!function_exists('browser')) {
    function browser()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
}

if (!function_exists('activation_code')) {
    function activation_code()
    {
        return sha1(time() . microtime());
    }
}

if (!function_exists('asset')) {
    /**
     * Function to access public folder
     */
    function asset($asset = null)
    {
        $assetPath = get_http_protocol() . '://' . base_url() . '/public/';
        if (is_null($asset)) {
            return $assetPath;
        }
        return $assetPath . $asset;
    }
}

if (!function_exists('get_http_protocol')) {
    /**
     * Function to get if server is running on HTTPS or HTTP.
     */
    function get_http_protocol()
    {
        return !empty($_SERVER['HTTPS']) ? "https" : "http";
    }
}

