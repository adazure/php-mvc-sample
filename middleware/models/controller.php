<?php

class Controller
{
    public function view($viewName, $data = [])
    {
        extract($data);
        require $_SERVER["DOCUMENT_ROOT"] . '/views/' . $viewName . '.php';
    }

}
