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

    private static function getallheaders2()
    {
        $headers = [];
      
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public static function getHeader($name)
    {
        try {
            $header = Helper::getallheaders2();
            foreach ($header as $headers => $value) {
                if ($headers == $name) {
                    return true;
                }

            }
            
        } catch (Exception $th) {

        }

        return false;
    }

    public static function getData($data, $route)
    {
        if (isset($data)) {
            foreach ($route as $value) {
                if (isset($data[$value])) {
                    $data = $data[$value];
                } else {
                    $data = "";
                }
            }
        }

        return $data;
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
        if (!isset($_SESSION[get_session_user_key])) {
            return false;
        }

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
