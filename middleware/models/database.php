<?php
class Database
{
    protected $database;

    public function __construct(){
        try{
            $this->database = new PDO('');
        }
        catch(PDOException $ex){
            echo $ex->getMessage();
        }
    }
}
