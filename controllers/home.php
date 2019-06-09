<?php
class Home extends Controller
{
    public function Action()
    {
        $data = ['name'=>'John Smith'];



        self::view('home', $data);
    }

}
