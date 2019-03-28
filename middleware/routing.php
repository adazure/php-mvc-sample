<?php

Router::get('/test', 'testController', 'action');

Router::get('/test2', null, function () {
    echo 'Hello World';
});
