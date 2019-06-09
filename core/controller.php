<?php

require_once __DIR__ . '/renderer.php';
require_once __DIR__ . '/error.php';

class Controller extends Renderer
{

    protected function view(...$args)
    { 
        try {
            $count = count($args);
            $viewName = debug_backtrace()[1]['function'];
            $data = $count > 1 ? (empty($args[1]) ? [] : $args[1]) : [];
            $options = $count > 2 ? (empty($args[2]) ? [] : $args[2]) : [];
            $viewName = strtolower(empty($args[0]) ? $viewName : $args[0]);

            if (!file_exists(__WWW__ . $viewName . __fileext__)) {
                Error::view404();
            }
            $this->init($viewName, $data, $options);

        } catch (Exception $th) {
            Error::view404();
        }
    }
}
