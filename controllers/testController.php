<?php
class TestController extends Controller
{
    public function Action(){
        echo 'Hello World';
        self::view('test',[]);
    }
}
