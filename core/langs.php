<?php
define("__session_name__", "__CALTURE_NAME__");
define("__CALTURE_NAME__", isset($_SESSION[__session_name__]) ? $_SESSION[__session_name__] : 'en');

/**
 *
 *
 */
class Langs
{
    static $langData = [];

    public static function Set($calture, $data)
    {
        self::$langData[$calture] = $data;
    }

    public static function Get($key)
    {
        if (self::$langData[__CALTURE_NAME__] != null) {
            if (self::$langData[__CALTURE_NAME__][$key] != null) {
                return self::$langData[__CALTURE_NAME__][$key];
            }
        }

        return "";
    }
}

Helper::requireFiles(__TOP__ . 'langs/');
