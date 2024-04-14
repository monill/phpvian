<?php

namespace PHPvian\Libs;

class Session
{
    private $secure;

    public function __construct()
    {
        $this->secure = config('session', 'session_secure');
    }

    private function sessionSetting()
    {
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.use_cookies', 'On');
        ini_set('session.use_only_cookies', 'On');
        ini_set('session.use_strict_mode', 'On');
        ini_set('session.cookie_httponly', 'On');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_trans_sid', "Off");
        ini_set('session.trans_sid_hosts', '[limited hosts]');
        ini_set('session.trans_sid_tags', '[limited tags]');
        ini_set('session.referer_check', base_url());
        ini_set('session.cache_limiter', 'nocache');
        ini_set('session.sid_length', 48);
        ini_set('session.sid_bits_per_character', 6);
        if ($this->secure) {
            ini_set('session.cookie_secure', 'On');
        }
    }
    /**
     * Start session.
     */
    public static function startSession()
    {
        ini_set('session.name', 'PHPvian');
        ini_set("session.use_only_cookies", config('session', 'session_use_only_cookies'));

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params(
            $cookieParams["lifetime"],
            $cookieParams["path"],
            $cookieParams["domain"],
            config('session', 'session_secure'),
            config('session', 'session_http_only'),
        );

        session_start();
        session_regenerate_id(config('session', 'session_regenerate_id'));
    }

    /**
     * Destroy session.
     */
    public static function destroySession()
    {
        $_SESSION = [];

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 420000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
        session_destroy();
    }

    /**
     * Set session data.
     * @param string $key Key that will be used to store value.
     * @param mixed $value Value that will be stored.
     */
    public static function set($key, $value)
    {
        return $_SESSION[$key] = $value;
    }

    /**
     * Get data from $_SESSION variable.
     * @param string $key Key used to get data from session.
     * @param mixed $default This will be returned if there is no record inside
     * session for given key.
     * @return mixed Session value for given key.
     */
    public static function get($key, $default = null)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return $default;
        }
    }

    /**
     * Unset session data with provided key.
     * @param $key
     */
    public static function destroy($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}