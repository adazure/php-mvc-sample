<?php
class HomeController extends Controller
{
    public function Action()
    {
        $data = [
            "name" => "Arthur",
            "last" => "John Doe",
            "email" => "john@doe.com",
        ];
        self::view('home', $data, ['cache' => 1]);
    }
}
