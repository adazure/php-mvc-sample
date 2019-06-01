<?php

class Compiler
{

    protected function init($viewName, $data = [], $options = [])
    {
        $viewOptions = [
            'cache' => 0,
        ];

        if (isset($options)) {
            foreach ($options as $key => $value) {
                if (isset($viewOptions[$key])) {
                    $viewOptions[$key] = $options[$key];
                }
            }
        }
        
        $path = __TOP__ . 'cache/views.' . str_replace('/', '.', $viewName) . '.cache';

        $timeExpired = file_exists($path) ? time() - filemtime($path) > $viewOptions['cache'] * 60 : true;

        if ($viewOptions['cache'] >= 0 and $timeExpired) {

            $config = $this->render(file_get_contents(__TOP__ . '/views/' . $viewName . '.php'), ['temp' => [], 'data' => $data]);

            eval('?>' . $body);

        }

        unset($tempData);

    }

    private function render($body, $data)
    {

        if (preg_match_all("/{{([\w\.\-\_]+)\s([\w\.\-\_]+)}}/", $body, $list)) {

            $full = $list[0];
            $names = $list[1];
            $values = $list[2];

            foreach ($full as $index => $fullname) {
                if (is_callable(array('Compiler', '__' . $names[$index]))) {
                    $body = call_user_func(array('Compiler', '__' . $names[$index]), $body, $fullname, $names[$index], $values[$index], $data);
                }
            }

        }

        return $body;
    }

    private function __layout($body, $pattern, $name, $value, $data)
    {
        $lay = __LAY__ . str_replace('.', '/', $value) . '.php';
        if (file_exists($lay)) {
            $layContent = file_get_contents($lay);
            $body = str_replace('@body()', str_replace($pattern, null, $body), $layContent);
        }

        return $body;
    }

    private function __include($body, $pattern, $name, $value, $data)
    {
        $file = __TOP__ . str_replace('.', '/', $value) . '.php';
        if (file_exists($file)) {
        $content = $this->render(file_get_contents($file), $data);
            $body = str_replace($pattern, $content, $body);
        }

        return $body;
    }

    private function __print($body, $pattern, $name, $value, $data)
    {
        $result = "";

        if (!isset($data['tempData'][$pattern])) {

            if (isset($value)) {
                $ex = explode('.', $value);
                $f = $ex[0];
                $f_n = array_splice($ex, 1);
                $func = array('Compiler', 'print_' . $f);
                if (is_callable($func)) {

                    $result = call_user_func($func, $data, $f_n);
                    $data['tempData'][$pattern] = $result;
                }
                unset($func);
            }
        } else {
            $result = $data['tempData'][$pattern];
        }

        print_r($data);

        $body = str_replace($pattern, $result, $body);

        return $body;
    }

    private function print_data($data, $fn)
    {
        return Helper::getData($data['data'], $fn);
    }

    /**
     *
     */
    // private function parser($content, $data = null, $options = null)
    // {
    //     $parseCollection = ['layout', 'lang', 'data'];
    //     foreach ($parseCollection as $value) {
    //         $content = call_user_func(array('Compiler', $value), $content, $data);
    //     }
    //     return $content;
    // }

    // /**
    //  * params {viewname} Görüntülenecek view dosyasının adı
    //  * params {options} Sayfa ile ilgili ayar bilgileri
    //  *          - Cache = Dakika cinsinden 0 ve üzeri
    //  */
    // protected function render($viewName, $data = null, $options = null)
    // {

    //     $viewOptions = [
    //         'cache' => 0, // Varsayılan olarak 1 dk oluşturuldu
    //     ];

    //     /**
    //      * Parametre olarak gelen $options değerindeki tüm alanlar, sadece viewOptions içerisinde bulunan değerlerle eşleşiyorsa kabul edilir.
    //      */
    //     if (isset($options)) {
    //         foreach ($options as $key => $value) {
    //             if (isset($viewOptions[$key])) {
    //                 $viewOptions[$key] = $options[$key];
    //             }
    //         }
    //     }

    //     /**
    //      * Cache dosya yolu
    //      */
    //     $path = __TOP__ . 'cache/views.' . str_replace('/', '.', $viewName) . '.cache';

    //     /** File Cache Time */

    //     $timeExpired = file_exists($path) ? time() - filemtime($path) > $viewOptions['cache'] * 60 : true;

    //     if ($viewOptions['cache'] >= 0 and $timeExpired) {

    //         /** Tampon oluştur */
    //         ob_start();

    //         /** View dosya içeriğini al ve parser methodundaki döngüden geçir.
    //          * Sonuç olarak tamamlanmış bir data geri döndür
    //          */
    //         $body = $this->parser(file_get_contents(__TOP__ . '/views/' . $viewName . '.php'), $data, $options);

    //         /**
    //          * Cache için oluşturulan datayı çalıştır.
    //          */

    //         /**
    //          * İçeriği al ve bir dosyaya yazdır
    //          */
    //         file_put_contents($path, ob_get_contents());

    //         /**
    //          * Tamponu temizle
    //          */
    //         ob_clean();

    //     }

    //     /**
    //      * Oluşturulmuş cache dosyasını ekrana getir
    //      */
    //     include $path;
    // }

    // protected function layout($content, $data = null)
    // {
    //     /** Metin içerisinde layout ibaresini arayacağız*/
    //     $pattern = "/\{layout (.*)\}/";

    //     /** Layout ibaresi varsa $match değerinden yakalayalım */
    //     if (preg_match($pattern, $content, $match)) {

    //         /** Layout dosyasının yolunu oluşturalım */
    //         $path = __LAY__ . $match[1] . '.php';

    //         /** Layout dosyası varsa işleme devam et */
    //         if (file_exists($path)) {

    //             /** Layout dosyasının içeriğini al */
    //             $layout_content = file_get_contents($path);
    //             /** {body content} değeriyle içeriği değiştir */
    //             $content = str_replace("<layout-body/>", str_replace($match[0], "", $content), $layout_content);
    //         }
    //     }

    //     return $content;
    // }

    // protected function lang($content, $data = null)
    // {
    //     $pattern = "/{lang (\w+)}/";
    //     if (preg_match_all($pattern, $content, $matches)) {
    //         foreach ($matches[0] as $key => $value) {
    //             $content = str_replace($value, Langs::get($matches[1][$key]), $content);
    //         }
    //     }

    //     return $content;
    // }

    // protected function data($content, $data = [])
    // {
    //     $pattern = "/{data (\w+)}/";
    //     if (preg_match_all($pattern, $content, $matches)) {
    //         foreach ($matches[0] as $key => $value) {
    //             $content = str_replace($value, isset($data[$matches[1][$key]]) ? $data[$matches[1][$key]] : "", $content);
    //         }
    //     }

    //     return $content;
    // }
}
