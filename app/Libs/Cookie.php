<?php

namespace PHPvian\Libs;

class Cookie
{
    public static function exists($name)
    {
        return isset($_COOKIE[$name]);
    }

    public static function get($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    public static function set($name, $value, $expiry)
    {
        $result = setcookie($name, $value, time() + $expiry, "/");
        if (!$result) {
            return false;
        }
        return true;
    }

    public static function destroy($name)
    {
        return self::set($name, "", time() - 1);
    }
}
