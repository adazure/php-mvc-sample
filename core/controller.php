<?php

class Controller
{
    public function view($viewName, $data = [])
    {
        if (isset($data)) {
            extract($data);
        }
        require __TOP__ . '/views/' . $viewName . '.php';

    }

}
