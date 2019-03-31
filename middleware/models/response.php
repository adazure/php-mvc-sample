<?php

class Response
{
    public static function redirect($url)
    {
        header("Location: " . (!empty($url) ? $url : '/'), true);
        exit();
    }

    public static function error($code)
    {
        http_response_code($code);
    }

    public static function referer()
    {
        self::redirect(__REFERER__);
    }
}
