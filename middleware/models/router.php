<?php

class Router
{
    /**
     * Gösterim sırasında eğer eşlesen controller varsa bu durum true olarak işaretlenecek
     * ve diğer controller'lar görüntülenmeyecek
     */
    private static $isActive = false;

    public static function parse_regex($url)
    {
        $result = [
            'url' => $url,
            'keys' => array(),
        ];

        if (preg_match_all('@\{([a-zA-Z0-9]+)\}@', $url, $args)) {
            foreach ($args[0] as $key => $value) {
                $result['url'] = str_replace($value, '([a-zA-Z0-9]+)', $result['url']);
                $result['keys'][$key] = str_replace(['{', '}'], '', $value);
            }
        }

        return $result;
    }

    public static function parse_url($args)
    {
        /**
         * Mevcut görüntülenen controller varsa bu alanı işletmiyoruz
         * */

        if (self::$isActive) {
            return;
        }

        /**
         * Method bilgisini aramak için uygun hale getirelim
         */
        $method = explode('|', strtoupper($args[2]));

        /**
         * Sayfa method bilgisi ile bizden istenen method bilgisi eşleşiyorsa devam et
         */
        if (in_array($_SERVER['REQUEST_METHOD'], $method)) {

            $withPattern = self::parse_regex($args[0]);

            /** İstenen pattern bilgisine eş değerse devam et */
            if (preg_match('@^' . $withPattern['url'] . '$@', $_SERVER["REQUEST_URI"], $params)) {

                self::$isActive = true;

                array_splice($params, 0, 1);

                foreach ($params as $key => $value) {
                    $params[$withPattern['keys'][$key]] = $value;
                    unset($params[$key]);
                }

                /** Çalışırılabilir bir method varmı */
                if (is_callable($args[1])) {

                    /** Methodu çalıştır */
                    call_user_func_array($args[1], $params);
                    /** */

                } else {

                    /** Eğer çalıştırılabilir bir method yoksa Controller var mı ona bakalım */
                    $controllerEx = explode('@', $args[1]); //folder.controller@
                    $controllerFolderStr = str_replace('.', '/', $controllerEx[0]);
                    $controllerName = explode(".", $controllerEx[0]);
                    $controllerName = end($controllerName);
                    $dir = $_SERVER["DOCUMENT_ROOT"] . '/controllers/' . $controllerFolderStr . '.php';
                    if (file_exists($dir)) {
                        require $dir;
                        call_user_func_array([new $controllerName, $controllerEx[1]], [$params]);
                    }
                }
            }
        }
    }

    public static function get($url, $action)
    {
        self::parse_url([$url, $action, 'GET']);
    }

    public static function post($url, $action)
    {
        self::parse_url([$url, $action, 'POST']);
    }

}
