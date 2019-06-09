<?php

/**
 * ROUTING
 */
$router = new Router;

/**
 * get('[URL-MAP]','[CONTROLLER]','[ACTION]')
 */
$router

    ->get('/', 'home', 'action')
    ->run();

/**
 * Remove Cache
 *
 *  */
unset($router);
