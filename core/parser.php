<?php

class Parser
{

    /**
     *
     */
    private function viewParser($content, $data = null, $options = null)
    {
        $parseCollection = ['viewLayout', 'viewLang', 'viewData'];
        foreach ($parseCollection as $value) {
            $content = call_user_func(array('Parser', $value), $content, $data);
        }
        return $content;
    }

    /**
     * params {viewname} Görüntülenecek view dosyasının adı
     * params {options} Sayfa ile ilgili ayar bilgileri
     *          - Cache = Dakika cinsinden 0 ve üzeri
     */
    protected function viewRender($viewName, $data = null, $options = null)
    {

        $viewOptions = [
            'cache' => 0, // Varsayılan olarak 1 dk oluşturuldu
        ];

        /**
         * Parametre olarak gelen $options değerindeki tüm alanlar, sadece viewOptions içerisinde bulunan değerlerle eşleşiyorsa kabul edilir.
         */
        if (isset($options)) {
            foreach ($options as $key => $value) {
                if (isset($viewOptions[$key])) {
                    $viewOptions[$key] = $options[$key];
                }
            }
        }

        /**
         * Cache dosya yolu
         */
        $path = __TOP__ . 'cache/views.' . str_replace('/', '.', $viewName) . '.cache';

        /** File Cache Time */

        $timeExpired = file_exists($path) ? time() - filemtime($path) > $viewOptions['cache'] * 60 : true;

        if ($viewOptions['cache'] >= 0 and $timeExpired) {

            /** Tampon oluştur */
            ob_start();

            /** View dosya içeriğini al ve parser methodundaki döngüden geçir.
             * Sonuç olarak tamamlanmış bir data geri döndür
             */
            $body = $this->viewParser(file_get_contents(__TOP__ . '/views/' . $viewName . '.php'), $data, $options);

            /**
             * Cache için oluşturulan datayı çalıştır.
             */
            eval('?>' . $body);

            /**
             * İçeriği al ve bir dosyaya yazdır
             */
            file_put_contents($path, ob_get_contents());

            /**
             * Tamponu temizle
             */
            ob_clean();

        }

        /**
         * Oluşturulmuş cache dosyasını ekrana getir
         */
        include $path;
    }

    protected function viewLayout($content, $data = null)
    {
        /** Metin içerisinde layout ibaresini arayacağız*/
        $pattern = "/\{layout (.*)\}/";

        /** Layout ibaresi varsa $match değerinden yakalayalım */
        if (preg_match($pattern, $content, $match)) {

            /** Layout dosyasının yolunu oluşturalım */
            $path = __LAY__ . $match[1] . '.php';

            /** Layout dosyası varsa işleme devam et */
            if (file_exists($path)) {

                /** Layout dosyasının içeriğini al */
                $layout_content = file_get_contents($path);
                /** {body content} değeriyle içeriği değiştir */
                $content = str_replace("{body content}", str_replace($match[0], "", $content), $layout_content);
            }
        }

        return $content;
    }

    protected function viewLang($content, $data = null)
    {
        $pattern = "/{lang (\w+)}/";
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $key => $value) {
                $content = str_replace($value, Langs::get($matches[1][$key]), $content);
            }
        }

        return $content;
    }

    protected function viewData($content, $data = [])
    {
        $pattern = "/{data (\w+)}/";
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $key => $value) {
                $content = str_replace($value, isset($data[$matches[1][$key]]) ? $data[$matches[1][$key]] : "", $content);
            }
        }

        return $content;
    }
}
