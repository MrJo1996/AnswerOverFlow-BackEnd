<?php

class DBConnectionManager
{
    private $connection;
    private $host = "localhost";
    private $username = "root";
    private $passwd = "";
    private $dbname = "my_answeroverflow";

    function runConnection()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->passwd, $this->dbname);
        return $this->connection;
    }
}

?>