<?php

class Response
{

    /**
     * 
     * İstenilen url bilgisine yönlendirme işlemi yapılmaktadır.
     * 
     */
    public static function redirect($url)
    {
        header("Location: " . (!empty($url) ? $url : '/'), true);
        exit();
    }


    /**
     * 
     * Sayfada hata göstermi için kullanılan method
     * Parametre olarak 404, 500, 403, 200 vs gibi sunucu kodları gönderilmektedir.
     * 
     */
    public static function error($code)
    {
        header('Error:Page', true, $code);
        exit();
    }

    /**
     * 
     * Çağırıldığında bir önceki gelinen sayfaya yönlendirme yapılıyor
     * 
     */
    public static function referer()
    {
        self::redirect(__REFERER__);
    }
}
