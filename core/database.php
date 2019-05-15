<?php
class Database
{
    private $ip = '91.93.113.229';
    private $dbname = 'admin_junkgrapDB';
    private $dbuser = 'admin_clico';
    private $dbpass = 'fXnHgNP4d3';
    private $db;

    public function __construct()
    {
        $format = sprintf('mysql:host=%s;dbname=%s;charset=utf8', $this->ip, $this->dbname);
        $this->db = new PDO($format, $this->dbuser, $this->dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    private function query($query)
    {
        $statement = $this->db->prepare($query);
        $statement->execute();
        return $statement;
    }

    public function select($query)
    {
        return $this->query($query)->fetch(PDO::FETCH_ASSOC);
    }

    public function selectAll($query)
    {
        return $this->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($tablename, $data)
    {
        $insertId = 0;
        $values = [];
        foreach ($data as $key => $value) {
            $values[] = in_array(gettype($value), ['string', 'datetime']) ? "'" . $value . "'" : $value;
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)"
            , $tablename, implode(',', array_keys($data)), implode(',', $values));

        unset($values);

        try {
            $statement = $this->db->prepare($sql);
            $result = $statement->execute();
            $insertId = $this->db->lastInsertId();
        } catch (Exception $th) {
            echo $th->getMessage();
        }

        return $insertId;
    }

}
