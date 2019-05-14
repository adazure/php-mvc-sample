<?php

class Router
{

    /**
     * Şartlara uygun bir rota yönlendirmesi var mı yok mu durumunu tutan değişken
     * URL ile Rota eşleşmesi olduğunda true olacak ve diğer işlemler gerçekleştirilmeyecek
     */
    private $IS_ACTIVE = false;

    /**
     * Oluşturulan tüm rotaların içerisinde tutan değişken
     */
    private $ROUTES_DATA = [];

    /**
     * URL bilgisindeki değerler ile, gelen map url bilgisi karşılaştırması ve ayıklanması işlemlerini yürütür
     */

    private function parse_regex($map, $action)
    {
        // try {
        /**
         * İşlemler sırasında kullanılacak değerleri big alanda topluyoruz
         */
        $cache = [
            'pattern' => $map,
            'stringMatch' => '[a-zA-Z0-9_-]+',
            'anyMatch' => '@\:[\[\]\(\)\w+0-9\\\,\{\}_-]+@',
        ];

        /**
         * İşlem sonrası methodlara gönderilecek olan, url bilgilerindeki parametreleri atayacağımız array nesnesi.
         */
        $parameters = [];

        /**
         * Methoda gönderilen map rota bilgisi içerisinde herhangi bir regex değer bulunuyor mu bu kontrol ediliyor
         * Regex tanımlamalar /: slaş işaretinden sonra iki nokta koyarak tanımlanıyor
         * /: ile tanımlanan tüm değerleri parametre içerisinden ulaşılacak anlamı taşımaktadır.
         * Bu tanımlamalara örnek;
         * /:[a-z]+
         * /:[\d]+
         * /:[\w]{1,3}
         *
         * Buradaki tanımlamalar ise [a-zA-Z0-9_-]+ tanımına eş değerdir.
         * Direk olarak bir isim yazılabilir.
         * /:id
         * /:product
         * /:cityName
         * /:param1
         * /:param2
         */
        if (preg_match_all($cache['anyMatch'], $map, $args)) {

            /**
             * Bulunan tüm /:[], /:name gibi değerler işleme alınıyor
             */
            foreach ($args[0] as $key => $value) {
                /**
                 * /: ifadesindeki ":" işareti kaldırılıyor
                 */
                $value = substr($value, 1);

                /**
                 * Öncelikle gelen value değeri /:name şeklinde bir değer mi buna bakılıyor
                 * Eğer değer eşleşiyorsa dönüş olarak cache['stringMatch'] regex değeri gönderiliyor, değilse gelen değer döndürülüyor
                 */

                $val = preg_match('/^' . $cache['stringMatch'] . '$/', $value) ? $cache['stringMatch'] : $value;

                /**
                 * Son olarak da gelen map rota dizimi uygun hale getiriliyor
                 */

                $cache['pattern'] = str_replace($args[0][$key], '(' . $val . ')', $cache['pattern']);

            }/** foreach */

        }/** If preg_match_all */

        /**
         * Çağırılan url bilgisi içerisinde, tamamlamış olduğumuz map rotasını sorguluyoruz
         * Eğer uygun olan rota bulunursa işleme alıyoruz
         */
        if (preg_match_all('@^' . $cache['pattern'] . '$@', $_SERVER["REQUEST_URI"], $params)) {
            /**
             * Listedeki ilk değeri siliyoruz
             */
            unset($params[0]);

            /**
             * Geri döndürülecek parametreler varsa onları listeye ekliyoruz
             */
            foreach ($params as $key => $value) {
                $parameters[$key] = $value[0];
            }

            /**
             * Eşleşen bir rota olduğuna dair değişkenimizin durumunu true olarak çeviriyoruz
             * Artık bu noktadan sonra gelecek olan hiç bir rota işleme alınmayacak
             */
            $this->IS_ACTIVE = true;
        }

        /**
         * Hafızadan kullanılan değerleri silelim
         */
        unset($cache);

        /**
         * Eğer yukarıdaki tüm işlemler sonucu eşleşme bulunduysa;
         * Bu rotaya ait kalan son işlemleri tamamlıyoruz
         */
        if ($this->IS_ACTIVE) {

            /** Method içerisine parametre olarak çalıştırılması istenen bir method gönderildiyse çalıştır */
            if (is_callable($action)) {
                call_user_func_array($action, [$parameters]);
            }
            /**
             * Yok hayır bir fonksiyon yok. O zaman sadece geriye bulunan parametreleri gönder
             */
            else {
                return [$parameters];
            }
        }/** If this isActive */

        // } catch (Exception $ex) {
        //     http_response_code(500);
        // }
    }

    /**
     * Gönderilen değerlerdeki rota,controller ve action değerlerine göre işlemleri sorgular
     */
    private function set_url($args)
    {

        /**
         * İlgili rotayı sorgulamadan önce eşlesen rota varsa işlemi iptal et
         */
        if ($this->IS_ACTIVE) {
            return;
        }

        /**
         * Gelen method bilgisini al. GET|POST|PUT|DELETE
         */
        $method = explode('|', strtoupper($args['method']));

        /**
         * Method bilgisi sayfaya yapılan isteğin tipiyle karşılaştırılıyor
         * Eğer eşleşme varsa devam ediyor
         */
        if (in_array($_SERVER['REQUEST_METHOD'], $method)) {

            /**
             * Gelen rota bilgisini sorgulamak için ilgili methoda iletiyoruz
             */
            $this->parse_regex($args['map'], function ($params = []) use ($args) {

                /**
                 * Eşleşme bulunduğundan dolayı bu scope alanına girdik
                 * Şimdiki işlem gelen arguman değerleri içeriside action alanı çalıştırılabilir bir method mu bu kontrol ediiyor
                 */
                if (is_callable($args['action'])) {

                    /**
                     * Method bilgisi bulundu. Bu methodu çalıştıralım ve parametreleri iletelim
                     */
                    call_user_func_array($args['action'], $params);

                }/** is callable */

                /**
                 * Action değeri çalıştırılabilir bir method değil
                 * O halde bu bir controller sınıfının alt methodu olarak kabul ediliyor
                 */
                else {

                    /**
                     * Controller alanı içinde iki şekilde yol tarifi yapabiliriz
                     * Ana dizin olarak Controller klasörü altında arama biçimleri;
                     * Varsa alt klasörlerle birlikte tanımlama:
                     *
                     * 1- productController
                     * 2- products.productController
                     * 3- products/productController
                     */

                    /**
                     * "." işaretli bir tanımlama varsa değiştiriliyor
                     * Burada istersek regex ile kontrol ederek uygun bir durum oluşturabilir ve uyarı verebiliriz.
                     */
                    $controller = str_replace('.', '/', $args['controller']);

                    /**
                     * Dosya yolunu oluşturuyoruz
                     */
                    $dir = __TOP__ . 'controllers/' . $controller . '.php';

                    /**
                     * Dosya var mı kontrol ediliyor
                     */
                    if (file_exists($dir)) {
                        /**
                         * Bulunan dosya çağırılıyor ve oluşturuluyor
                         * İçerisindeki ilgili method çağırılıyor ve parametreler gönderiliyor
                         */
                        require $dir;
                        call_user_func_array([new $controller, $args['action']], $params);


                    }/** If Exist */
                }/** Else */

            }); /** Parse Regex Method */

        }/** If */

        /**
         * Hafızadan siliyoruz
         */
        unset($args);
    }


    /**
     * GET rota oluşturma methodu
     */
    public function get($map, $controller = null, $action = null, $options = null)
    {
        $this->ROUTES_DATA[] = ['map' => $map, 'controller' => $controller, 'action' => $action, 'method' => 'GET', 'option' => $options];
        return $this;
    }

    /**
     * POST rota oluşturma methodu
     */
    public function post($map, $controller = null, $action = null)
    {
        $this->ROUTES_DATA[] = ['map' => $map, 'controller' => $controller, 'action' => $action, 'method' => 'POST', 'option' => null];
        return $this;
    }

    /**
     * Oluşturulan tüm rotaları çalıştırma methodu
     * Parametre olarak bir method değer alıyor
     * Action değeri, eğer işlem başarısız olursa çalıştırılıyor.
     * Bir nevi hata sayfası yönetmek için
     */
    public function run($action = null)
    {
        /**
         * Var olan tüm rotalar döngüye alınıyor
         */
        foreach ($this->ROUTES_DATA as $route) {

            /**
             * İşlem sırasında eğer bir önceki rota eşleşmişse işlemi iptal ediyoruz
             */
            if ($this->IS_ACTIVE) {
                break;
            }

            /**
             * Değilse sıradaki rotayı işleme almasını istiyoruz
             */
            $this->set_url($route);
        } /* foreach*/

        /**
         * Yukarıdaki işlemler bittiğinde hala bir eşleşme yoksa
         */
        if (!$this->IS_ACTIVE) {

            /** Gelen bir method varsa çalıştır  */
            if (is_callable($action)) {
                call_user_func($action);
            }
            /**
             * Değilse sayfa bulunamadı olarak işaretle
             **/
            else {
                http_response_code(404);
            }/** Else */
        }/** If */

    }

    /**
     * Sınıf kaldırıldığında hafızadan silelim
     */
    public function __destruct()
    {
        unset($this->IS_ACTIVE);
        unset($this->ROUTES_DATA);
    }
}
