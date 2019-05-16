<?php

include __DIR__ . '/parser.php';
include __DIR__ . '/error.php';

class Controller extends Parser
{

    protected function view(...$args)
    {
        $count = count($args);
        $viewName = debug_backtrace()[1]['function'];;
        $data = null;
        $options = null;

        /** View, Data, Options */
        if ($count == 3 and is_string($args[0]) and is_array($args[1]) and is_array($args[2])) {
            $viewName = $args[0];
            $data = $args[1];
            $options = $args[2];
        }
        /** View, Data */
        else if ($count == 2 and is_string($args[0]) and is_array($args[1])) {
            $viewName = $args[0];
            $data = $args[1];
        }
        /** Data, Options */
        else if ($count == 2 and is_array($args[0]) and is_array($args[1])) {
            $data = args[0];
            $options = args[1];
        }
        /** Data*/
        else if ($count == 1 and is_array($args[0])) {
            $data = $args[0];
        }
        /** View*/
        else if ($count == 1 and is_string($args[0])) {
            $viewName = $args[0];
        }

        if (!file_exists(__WWW__ . $viewName . '.php')) {
            Error::view404();
        }

        if (isset($data)) {
            extract($data);
        }

        unset($count);

        $this->viewRender($viewName, $data, $options);
    }

}
