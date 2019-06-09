<?php

class Renderer
{

    private $VALUES = [];
    private $DATA = [];
    private $OPTIONS = ['cache' => 0, 'cache_prefix' => '', 'cache_vars' => false];
    private $VIEW = "";
    private $TIME_EXPIRED = 0;
    private $CACHE_FILE = null;
    private $VIEW_URL = null;
    private $INCLUDES = [];
    private $HEADER = null;
    public $ISROOT = [] || false;
    private $REGEX = [
        'layout' => '/<g-layout\sname\s*=\s*\"(.*?)\"\s*\/>/',
        'include' => '/<g-include\s*name\s*=\s*\"(.*?)\"\s*\/>/',
        'Queries' => '/(data|request|session|lang)([\.\w]+)/', // data.user.name vs session.user.id vs
        'Body' => '/<g-body\s*\/>/',
        'If' => '/<g-if\s*condition\s*=\s*\"(.*?)\"\s*>/',
        'For' => '/<g-for\s*condition\s*=\s*\"(.*?)\"\s*>/',
        'Foreach' => '/<g-foreach\s*condition\s*=\s*\"(.*?)\"\s*>/',
        'ElseIf' => '/<g-elseif\s*condition\s*=\s*\"(.*?)\"\s*\/>/',
        'Else' => '<g-else>',
        'EndIf' => '</g-if>',
        'EndFor' => '</g-for>',
        'EndForeach' => '</g-foreach>',
        //'CommentJS' => '/\/\*[\s\S]*?\*\/|([^:]|^)\/\/.*$/',
        'CommentHTML' => '/(?:\<!--(?:.*?\r?\n?)*--\>)/',
        'Header' => '/(?:\<g-header\>((?:.*?\r?\n?)*)<\/g-header\>)/',
        'KeyValue' => '/{(\w+){1}\s+(.*?)}/', // print(0) data.user.name(1)

    ];

    /**
     *
     *
     * INIT METHOD
     *
     * Controller sınıfına ait view dosyasının ilk çağırıldığı method.
     * Bu alandan yönelerek ilgili datalar parse edilecektir.
     *
     * param viewname    :  Yüklenecek olan sayfanın adı "views" klasöründeki karşılığı
     * param data        :  Controller/action sınıfından gönderilen veriler ['name'=>'John','last'=>'Smith'] vs
     * param options     :  Ekstra olarak gönderilecek veriler ['cache'=>10,vs]
     *
     */

    protected function init($viewname, $data = [], $options = [])
    {
        /** Sınıf içerisinden kullanabilmek için ilgili alanlara atamaları yapıyoruz */
        $this->DATA = $data;
        $this->VIEW = $viewname;

        /** Gelen ekstra alanları bizim ilgili değişkenimizdeki alanları bulunduranları alalım */
        if (isset($options) && count($options) > 0) {
            foreach ($options as $key => $value) {
                if (isset($this->OPTIONS[$key])) {
                    $this->OPTIONS[$key] = $options[$key];
                }
            }
        }

        /** Cachelemeye querystring değerleri dahil edilsin mi sorgulanıyor */
        $querystr = "";
        if ($this->OPTIONS['cache_vars']) {
            $uri = $_SERVER["REQUEST_URI"];
            $pos = strpos($uri, '?');
            if ($pos) {
                $querystr = substr($uri, $pos + 1, strlen($uri));
                $querystr = preg_replace(array('[\W+]'), array('_'), $querystr);
            }
            unset($uri, $pos);
        }

        /** İşlem sonrası ilgili render verilerinin, içerisine yükleneceği cache dosyasının yolu */
        $this->CACHE_FILE = __TOP__ . 'cache/views.' . str_replace('/', '.', $viewname) . $this->OPTIONS['cache_prefix'] . $querystr . '.php';

        /** Cache durumu ilgili view için aktif ise süresini kontrol eder. */
        $this->TIME_EXPIRED = file_exists($this->CACHE_FILE) ? time() - filemtime($this->CACHE_FILE) > $this->OPTIONS['cache'] * 60 : true;

        /** İlgili view için cacheleme süresi dolmuşsa veya cache işlemi yapılmıyorsa, dosyayı yeniden render eder */
        if ($this->OPTIONS['cache'] >= 0 and $this->TIME_EXPIRED) {

            /** View dosyasının yolunu oluşturuyoruz */
            $this->VIEW_URL = __TOP__ . '/views/' . $viewname . __fileext__;

            /** View dosyası var mı kontrol et */
            if (file_exists($this->VIEW_URL)) {
                /** İlgili view dosyasının içeriğini çözümleyelim */
                $render_view = $this->render($this->VIEW_URL);

                $render_view = $this->renderHeader($render_view);

                /** Dosyayı kaydedelim */
                file_put_contents($this->CACHE_FILE, $render_view);
                
                unset($cont, $render_view);
            }

        }

        /** Oluşturulmuş sayfayı çekelim */
        include $this->CACHE_FILE;

        /** Tüm dataları sıfırlayalım */
        unset(
            $this->OPTIONS,
            $this->VALUES,
            $this->DATA,
            $this->VIEW,
            $this->TIME_EXPIRED,
            $this->CACHE_FILE,
            $this->VIEW_URL,
            $this->HEADER,
            $querystr
        );
    }

    /**
     *
     * RENDER METHOD
     *
     * Render methodu ilgili içeriği en baştan itibaren parse etmeye başlar
     * Öncelikle ilgili view dosyasının layout sınıfı var mı kontrol eder.
     * Eğer varsa değiştir ve içeriği geri gönderir
     *
     */

    private function render($file)
    {
        /** Gelen ilgili view dosyasının içeriğini al */
        $file_content = file_get_contents($file);

        /** Dosya içeriğini if else süzgecinden geçir. Böylece kontrol sırasında yüklenmesi istenmeyen içerikler yüklenmeyecek ve sorgudan çıkartılacaklar */
        $file_content = $this->ob_ifElse($file_content);

        /** Yorum satırlarını kaldır */
        $file_content = $this->removeComments($file_content);

        /** Varsa header bilgileri */
        $file_content = $this->setHeaderContent($file_content);

        /** İçeriğe son halini verelim. İçerisindeki tüm {key value} alanlarını değiştirelim */
        $file_content = $this->ob_lastContents($file_content);

        /** Varsa döngüleri oluştur */
        $file_content = $this->ob_for($file_content);

        /** Varsa döngüleri oluştur */
        $file_content = $this->ob_foreach($file_content);

        /** Oluşturulmuş dosya içeriğini çalıştır ve geri döndür */
        $file_content = $this->ob_burn($file_content);

        /** Oluşturulmuş içeriği layout kontrolünden geçir */
        return $this->__layout($file_content);
    }

    /**
     *
     * LAYOUT METHOD
     *
     * View içeriğinin bir layout sınıfı içerip içermediği kontrol ediliyor
     * Eğer içeriyorsa, ilgili layout sayfasına aktarım yapılıyor
     *
     */
    private function __layout($viewContent)
    {

        /** İlgili içerikte "layout" değerleri aranıyor */
        if (preg_match($this->REGEX['layout'], $viewContent, $result)) {

            /** Bulunan değerler dosya yolu olarak sırasıyla oluşturuluyor */
            $layout_file = __LAY__ . str_replace('.', '/', $result[1]) . __fileext__;

            /** Layout Dosyanın varlığı kontrol ediliyor. */
            if (file_exists($layout_file)) {

                /** Layout dosya içeriği */
                $layout_content = file_get_contents($layout_file);

                /** Yorum satırlarını kaldır */
                $layout_content = $this->removeComments($layout_content);

                /** Layout dosyası If Else süzgecinden geçiriliyor */
                $layout_content = $this->ob_ifElse($layout_content);

                /** İçeriğe son halini verelim. İçerisindeki tüm {key value} alanlarını değiştirelim */
                $layout_content = $this->ob_lastContents($layout_content);

                /** Layout içeriği yakılıyor ve sonucu alınıyor */
                $layout_content = $this->ob_burn($layout_content);

                /** View içeriğindeki ilgili "layout" tanımı siliniyor */
                $viewContent = str_replace($result[0], '', $viewContent);

                /** Layout içeriğinde ki "body" alanı bulunup, view içeriğiyle değiştiriliyor */
                $viewContent = preg_replace($this->REGEX['Body'], $viewContent, $layout_content);

                unset($layout_content);
            }

            unset($layout_file);
        }

        /** Oluşturulan içerik içerisinde dahil eilmesi gereken alanlar varsa sırasıyla işlemden geçiyor */
        $viewContent = $this->__include($viewContent);

        /** Sonuç verisini geri döndürüyoruz */
        return $viewContent;
    }

    private function removeComments($content)
    {
        if (preg_match_all($this->REGEX['CommentHTML'], $content, $matches)) {
            $list = $matches[0];
            foreach ($list as $key => $value) {
                $content = str_replace($value, '', $content);
            }
            unset($list);
        }
        return $content;
    }

    private function renderHeader($content)
    {

        /** Önce sayfadaki değiştirilecek alanı (layout dosyasındakini) bul */
        if (preg_match('/(?:\@header\(\){((?:.*?\r?\n?)*)})/', $content, $matches)) {
            /** Değiştirilecek son header bilgisi varsa, layouttaki header alanıyla değiştir */
            if ($this->HEADER != null) {
                $content = str_replace($matches[0], $this->HEADER, $content);
            } else {
                /** Header alanında belirlenenleri bırak */
                $content = str_replace($matches[0], $matches[1], $content);
            }
        }
        return $content;
    }

    private function setHeaderContent($content)
    {

        if (preg_match_all($this->REGEX['Header'], $content, $matches)) {
            $full = $matches[0];
            $values = $matches[1];
            foreach ($full as $key => $value) {
                $this->HEADER = $values[$key];
            }
            $content = str_replace($full[$key], '', $content);

            unset($full, $values);
        }

        return $content;
    }

    /**
     *
     * BURN YAKICI METHOD
     *
     * Amacı ilgili içerikleri tampon üzerinde çalıştırıp, sonucu geri döndürmektir.
     * Burada ilgii içerikte işlemler sonrası "If else, echo , include vs" gibi her çağırıldığında varsa PHP verilerini çalıştırır.
     * Böylece sonucu görerek diğer işlemleri rahatlıkla devam ettirebiliriz
     */
    private function ob_burn($content)
    {
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();

    }

    /**
     *
     * INCLUDE METHOD
     *
     * Varsa igili içerikte include değerlerini arar ve değiştirir
     *
     */
    private function __include($content)
    {

        /** Include değerleri ara */
        if (preg_match_all($this->REGEX['include'], $content, $result)) {

            /**
             *  Bulunan değerlerin tam karşılıklarını tutar
             *  <g-include name="views/viewfile" /> gibi
             */
            $full = $result[0];

            /**
             * Bulunan değerlerin parantez içindeki değerlerini tutar
             * views/viewfile
             */
            $values = $result[1];

            /** Bulunan değerleri sırasıyla işleyelim */
            foreach ($values as $i => $val) {

                /** Gelen view dosyalarının yollarını oluşturalım */
                $file = __TOP__ . str_replace('.', '/', $val) . __fileext__;
                $fcontent = "<!--not included file -->";
                /** View dosyası var mı kontrol et */
                if (file_exists($file)) {

                    /**
                     * Her dosyası sıfırdan tekrar işleme koy
                     * Sırasıyla If, layout, include vs
                     */
                    $fcontent = $this->render($file);

                }
                /** Son olarak da full değerdeki tanımı <g-include ..> kısmını içerikle değiştir. */
                $content = str_replace($full[$i], $fcontent, $content);

            }

            /** Değerleri temizle */
            unset($file_render, $fcontent, $full, $values, $file);
        }

        return $content;
    }

    /**
     *
     * SESSION METHOD
     *
     * Session ile ilgili geri döndürülmesi gereken değerler varsa buradan işleme alınacak
     * Örneğin {print session.user} gibi yada herhangi bir içerikte "session." ile başlayan değerler var ise burada parse edilecekler
     *
     */
    private function __session($content)
    {
        return $content;
    }

    /**
     *  İçerik içerisinde varsa data, session vs gibi özel tanımlı içerikleri bulup değiştir
     *  Böylece if sorgularında doğru bir şekilde işlem yapılabilir
     */

    private function parse_condition($condition)
    {

        /** Sorgu değerlerini bulur */
        if (preg_match_all($this->REGEX['Queries'], $condition, $result)) {

            /** Bulduğu data, session, request vs gibi tanımları tam halini liste halinde tutar
             * data.user.name
             * session.user
             * request.query
             */
            $full = $result[0];
            /** Tanımların sadece noktaya kadar olan ilk değerini verir
             * data
             * session
             * request
             */
            $names = $result[1];

            /** Verileri işlemden geçir */
            foreach ($full as $key => $value) {

                /** Noktalardan parçalar
                 * "data.user.name"
                 * */
                $split = explode('.', $value);

                /**
                 *  İlk kayıttan sonrasını al.
                 * "user.name" */
                $list = array_splice($split, 1);

                /**
                 *
                 * İlk değere ait bu sınıf içerisinde bir method var mı kontrol et
                 *
                 * class Renderer{
                 *
                 *      private function __data();
                 *      private function __session();
                 *      private function __request();
                 *      ....
                 * }
                 *
                 *
                 */

                /** Method tanımını oluştur */

                $action = array($this, '__' . $names[$key]);

                $r = "";
                /** Çalıştırılabilir olup olmadığını kontrol et */
                if (is_callable($action)) {

                    /**
                     * Bulduğun ilgili methodu çalıştır ve parametre olarak, bulduğun datanın ilk kayıdından sonrasını yolla
                     * data.user.name => ['user','name']
                     */
                    $r = call_user_func($action, $list);

                }

                if (is_string($r)) {
                    $r = is_string($r) ? "'" . $r . "'" : $r;
                } else if (is_array($r)) {
                    $r = '((array)json_decode(\'' . json_encode($r) . '\'))';
                }

                $condition = str_replace($value, $r, $condition);
            }

            unset($names, $full, $inputs, $action, $r);
        }

        return $condition;
    }

    private function matches($name, $pattern, $content)
    {
        /**
         * İçerikte ilgili tanımları arayalım
         */

        if (preg_match_all($pattern, $content, $result)) {

            /** Bulunan regex kümesinin tam hallerinin listesi <g-if condition="....."> */
            $full = $result[0];

            /** Bulunan regex kümesinin paranter condition alanındaki verilerin listesi */
            $values = $result[1];

            /** Bulunan listeyi sırasıyla işlemden geçir */
            foreach ($values as $i => $val) {

                /** Yardımcı methoddan ilgiyi veriyi düzenlemesini istiyoruz */
                $condition = $this->parse_condition($val);

                /** Bulduğun tanımları düzenlenmiş içerikle değiştir */
                $content = str_replace($full[$i], '<?php ' . $name . '(' . $condition . '): ?>', $content);

            }

            unset($condition, $full, $values);
        }

        return $content;
    }

    /**
     *
     * IF ELSE METHOD
     *
     * Gelen içerik dosyasındaki If else değerlerini
     *
     */

    private function ob_ifElse($content)
    {

        /**
         * Gelen içerikte If değerlerini arıyoruz ve değiştirilmesi iştenen verileri giriyoruz
         * Match yardımcı methodumuza ilk olarak, bulunan if tanımının yerine geçecek olan değeri veriyoruz "If"
         * Daha sonra bulmasını istediğimiz if/else tanımı
         */

        /** İf İçin */
        $content = $this->matches('if', $this->REGEX['If'], $content);

        /** if else için */
        $content = $this->matches('elseif', $this->REGEX['ElseIf'], $content);

        /** Son olarak else ve end if için tanımları değiştiriyoruz */
        $content = str_replace(array($this->REGEX['Else'], $this->REGEX['EndIf']), array('<?php else: ?>', '<?php endif; ?>'), $content);

        return $content;
    }

    private function ob_for($content)
    {

        $content = $this->matches('for', $this->REGEX['For'], $content);

        $content = str_replace($this->REGEX['EndFor'], '<?php endfor; ?>', $content);

        return $content;
    }

    private function ob_foreach($content)
    {

        $content = $this->matches('foreach', $this->REGEX['Foreach'], $content);

        $content = str_replace($this->REGEX['EndForeach'], '<?php endforeach;?>', $content);

        return $content;
    }
    /**
     *
     * Tamamen oluşturulmuş içerik içerisinde son işlemleri yaptırır.
     * İçerik içerisinde barındırılan tüm {key value} değerlerini değiştirir.
     *
     */
    private function ob_lastContents($content)
    {

        /** Tüm key/value alanlarını bul */
        if (preg_match_all($this->REGEX['KeyValue'], $content, $matches)) {

            /** {print data.user.name} liste */
            $full = $matches[0];

            /** print, session, data, request vs.. */
            $keys = $matches[1];

            /** data.user.name, session.name vs.. */
            $values = $matches[2];

            /** İşleme alalım */
            foreach ($full as $key => $value) {

                /** Daha önce bulunmuş aynı tanıma sahip bir değer bulunduysa tekrar işletme. Yoksa devam et */
                if (!isset($this->VALUES[$value])) {

                    /** İlgili key değerine ait method tanımı */
                    $func = array($this, 'op_' . $keys[$key]);

                    /** Method varsa çalıştır */
                    if (is_callable($func)) {

                        /** ob_print('data.user.name') */
                        $response = call_user_func($func, $values[$key]);

                        /** Varsa gelen değeri değiştir */
                        $content = str_replace($value, $response, $content);

                        /** Bir daha taramamak için  */
                        $this->VALUES[$value] = $response;
                    }
                }
            }

            unset($full, $keys, $values, $func);

        }

        return $content;
    }

    private function __data($list)
    {
        return Helper::getData($this->DATA, $list);
    }

    private function op_print($value)
    {
        $nvalue = "<?=''?>";
        /** Eğer liste boş işe boş gönder */
        if (empty($value)) {
            return "";
        }

        /** Gelen value değeri içinde Queriesler var mı kontrol et */
        $nvalue = $this->parse_condition($value);

        return "<?=" . $nvalue . "?>";

    }

}
