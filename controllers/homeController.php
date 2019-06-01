<?php
class HomeController extends Controller
{
    public function Action()
    {
        self::view('home', ['full' => ['name' => 'Kerem', 'lastname' => 'YAVUZ'], 'login' => true, 'spoiler' => true, 'omaro' => true], ['cache' => 0]);
    }
}
