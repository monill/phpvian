<?php

use PHPvian\Libs\Lang;
use PHPvian\Models\Config;
use PHPvian\Libs\View;

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
     * @param null $data
     * @return string
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
            throw new Exception("Configuration file '<b>config/{$file}.php</b>' not found.");
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
            '0.0.0.0/8',
            '10.0.0.0/8',
            '127.0.0.0/8',
            '169.254.0.0/16',
            '172.16.0.0/12',
            '192.0.0.0/24',
            '192.168.0.0/16',
            '224.0.0.0/4'
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
                return !empty($_POST);
            case "GET":
                return !empty($_GET);
            default:
                return false;
        }
    }
}

// Function to obtain a specific value from the request
if (!function_exists('input')) {
    function input($value)
    {
        $inputValue = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$value])) {
            $inputValue = trim(strip_tags($_POST[$value]));
        } elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET[$value])) {
            $inputValue = trim(strip_tags($_GET[$value]));
        }
        return $inputValue;
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302)
    {
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
        $assetPath = get_http_protocol() . '://' . base_url() . '/';
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

if (!function_exists('translate')) {
    function translate($file, $translate)
    {
        $lang = new Lang();
        return $lang->get($file, $translate);
    }
}

if (!function_exists('error_response')) {
    function error_response($errors, $status = "error")
    {
        return json_encode(["status" => $status, "errors" => $errors]);
    }
}
if (!function_exists('tableRow')) {
    function tableRow($name, $details, $status)
    {
        // Set icon based on status
        $icon = ($status == '1') ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
        // Assemble the table row
        echo "<tr><td>$name</td><td>$details</td><td>$icon</td></tr>";
    }
}
if (!function_exists('isExtensionAvailable')) {
    function isExtensionAvailable($name)
    {
        return extension_loaded($name);
    }
}
if (!function_exists('checkFolderPerm')) {
    function checkFolderPerm($name)
    {
        // Verificar se a pasta existe
        if (!is_dir(dirname(__DIR__) . DIRECTORY_SEPARATOR . $name)) {
            return false;
        }

        // Verificar as permissões da pasta
        $perm = substr(sprintf('%o', fileperms(dirname(__DIR__) . DIRECTORY_SEPARATOR . $name)), -4);
        return $perm >= '0775';
    }
}

if (!function_exists('setting')) {
    function setting($value)
    {
        $config = new Config();
        return $config->getSettingValue($value);
    }
}

if (!function_exists('http_host')) {
    function http_host()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
}
if (!function_exists('connection_file')) {
    function connection_file() {
        return file_exists(dirname(__DIR__) . '/config/database.php') ? true : false;
    }
}
