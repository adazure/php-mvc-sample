<?php

session_start();
define("__TOP__", $_SERVER["DOCUMENT_ROOT"].'/', true);
define("__LAY__", __TOP__ . 'views/layouts/', true);
define("__TMP__", __TOP__ . 'views/temp/', true);
define("__MID__", __TOP__ . 'middleware/', true);
define("__session_name__", "__CALTURE_NAME__");
define("__CALTURE_NAME__", isset($_SESSION[__session_name__]) ? $_SESSION[__session_name__] : 'en');
define("__REFERER__", isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
