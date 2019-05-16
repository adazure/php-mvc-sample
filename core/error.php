<?php

class Error
{
    public static function view404()
    {
        header("HTTP/1.0 404 Not Found");
        exit();
    }
}
