<?php
class Database
{
    protected $database;

    public function __construct(){
        try{
            $this->database = new PDO('mysql:host=91.93.113.229;dbname=admin_junkgrapDB','admin_clico','V6LwmgHtV3');
        }
        catch(PDOException $ex){
            echo $ex->getMessage();
        }
    }
}
