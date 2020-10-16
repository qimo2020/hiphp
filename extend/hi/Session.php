<?php declare(strict_types=1);
namespace hi;

class Session {
    protected static $sessionName;
    public function __construct($sessionName) {
        self::$sessionName = $sessionName;
    }

    public function isSession(){
        if (!array_key_exists(self::$sessionName, (array)session())) {
            return false;
        }
        return true;
    }

    public function setSession($key, $val)
    {
        if (!array_key_exists(self::$sessionName, session())) {
            session(self::$sessionName, []);
        }
        session(self::$sessionName . '.' . $key, $val);
    }

    public function getSession($key)
    {
        if (!array_key_exists(self::$sessionName, (array)session())) {
            session(self::$sessionName, []);
        }
        if (array_key_exists($key, session(self::$sessionName))) {
            return session(self::$sessionName . '.' . $key);
        }
        return false;
    }

    public function getSessionId(){
        return \think\facade\Session::getId();
    }
}