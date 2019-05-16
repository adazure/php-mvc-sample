<?php

class Helper
{
    public static function dateNow()
    {
        return date("Y-m-d H:i:s");
    }

    public static function clearDataFromInjection($data)
    {

    }

    public static function getHeader($name)
    {
        $header = getallheaders();
        foreach ($header as $headers => $value) {
            //echo '$headers : '. $value;
            if ($value == $name) return true;
        }

        return false;
    }

    public static function pregFormat($format, $data)
    {
        return preg_match($format, $data);
    }

    /**
     * Kullanıcıya ait gelen JSON data verisini session üzerinde tutan method.
     */
    public static function setUser($data)
    {
        $_SESSION[get_session_user_key] = json_encode($data);
    }

    /**
     * Session üzerinde kullanıcıya ait oturum bilgilerini verir
     */
    public static function getUser()
    {
        /** return JSON */
        if(!isset($_SESSION[get_session_user_key])) return false;
        return json_decode($_SESSION[get_session_user_key], true);
    }

    public static function requireFiles($directory)
    {
        if ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    require $directory . $entry;
                }
            }
            closedir($handle);
        }
    }

}
