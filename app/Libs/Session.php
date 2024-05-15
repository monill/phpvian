<?php

namespace PHPvian\Libs;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

class Session
{
    private $secure;

    public function __construct()
    {
        $this->secure = config('session', 'session_secure');
        $this->sessionSetting();
    }

    private function sessionSetting()
    {
        // Cookie related settings
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.use_cookies', 'On');
        ini_set('session.use_only_cookies', 'On');
        ini_set('session.use_strict_mode', 'On');
        ini_set('session.cookie_httponly', 'On');
        ini_set('session.cookie_samesite', 'Lax');

        // Session transaction related settings
        ini_set('session.use_trans_sid', 'Off');
        ini_set('session.trans_sid_hosts', '[limited hosts]');
        ini_set('session.trans_sid_tags', '[limited tags]');

        // Referrer settings
        ini_set('session.referer_check', base_url());

        // Other settings
        ini_set('session.cache_limiter', 'nocache');
        ini_set('session.sid_length', 48);
        ini_set('session.sid_bits_per_character', 6);

        // Conditional settings
        if ($this->secure) {
            ini_set('session.cookie_secure', 'On');
        }
    }

    /**
     * Start session.
     */
    public static function startSession()
    {
        try {
            // Sets the session name
            session_name('PHPvian');

            // Sets session cookie parameters
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Start the session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Regenerates session ID to prevent session fixation attacks
            session_regenerate_id(true);
        } catch (Throwable $e) {
            // Handles any errors during session initialization
            error_log('Error when starting the session: ' . $e->getMessage());
            exit('An internal error has occurred. Please try again later.');
        }
    }

    /**
     * Destroy session.
     */
    public static function destroySession()
    {
        // Clear session data
        $_SESSION = [];
        // Gets session cookie parameters
        $params = session_get_cookie_params();
        // Set the session cookie to expire
        setcookie(session_name(), '', time() - 1, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        // Destroy the session
        session_destroy();
    }

    /**
     * Set session data.
     * @param string $key Key that will be used to store value.
     * @param mixed $value Value that will be stored.
     * @return mixed
     */
    public static function set($key, $value)
    {
        // Checks if the session is active
        if (!session_id()) {
            throw new RuntimeException('The session is not started.');
        }
        // Try to set the value in the session
        $_SESSION[$key] = $value;
        // Checks if the value was set successfully
        if (!isset($_SESSION[$key]) || $_SESSION[$key] !== $value) {
            throw new RuntimeException('Failed to set value in session.');
        }
        // Returns the defined value
        return $value;
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
        // Checks if the key is a valid string
        if (!is_string($key)) {
            throw new InvalidArgumentException('The session key must be a valid string.');
        }
        // Returns the value associated with the key in the session, or the default value if the key does not exist
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
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