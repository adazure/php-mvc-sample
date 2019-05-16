<?php

/**
 * ROUTING
 */
$router = new Router;

$router
    ->get('/', 'homeController', 'action')
    //->get('/detail/:name/:[\d]+','detailController','action')
    ->run();



/**
 * Remove Cache
 *
 *  */
unset($router);
