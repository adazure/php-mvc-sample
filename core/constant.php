<?php
/**
 * SABİTLER
 * Site genelinde kullanılacak olan sabit değişkenler burada tutuluyor
 *
 */

define("__TOP__", $_SERVER["DOCUMENT_ROOT"] . '/', true);
define("__WWW__", __TOP__ . 'views/', true);
define("__LAY__", __WWW__ . 'layouts/', true);
define("__TMP__", __WWW__ . 'temp/', true);
define("__COR__", __TOP__ . 'core/', true);
define("__MID__", __TOP__ . 'middleware/', true);
define("__REFERER__", isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
define("__CSRGRAP__", Helper::getHeader("HTTP_X_CSR_REQUEST"));

/**
 * Kullanıcı bilgileri için session kontrolü eklendi.
 */
define("get_session_user_key", "session_user_data");
define("get_session_user", isset($_SESSION[get_session_user_key]));
define("__session_name__", "__CALTURE_NAME__");
define("__CALTURE_NAME__", isset($_SESSION[__session_name__]) ? $_SESSION[__session_name__] : 'en');