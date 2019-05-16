<?php

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
            if (isset(self::$langData[__CALTURE_NAME__][$key])) {
                return self::$langData[__CALTURE_NAME__][$key];
            }
        }

        return "";
    }
}

Helper::requireFiles(__TOP__ . 'langs/');
