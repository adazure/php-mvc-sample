<?php

/**
 * Routing
 */
$router = new Router;

$router
    ->get('/', 'testController', 'action')
    ->run();

/**
 * Cache Clear
 */
unset($router);
