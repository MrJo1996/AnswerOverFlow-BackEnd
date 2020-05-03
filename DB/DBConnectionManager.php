<?php

class DBConnectionManager
{
    private $connection;
    private $host = "localhost";
    private $username = "root";
    private $passwd = "root";
    private $dbname = "answeroverflow"; //nome su phpMyAdmin

    function runConnection()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->passwd, $this->dbname);
        return $this->connection;
    }
}

?>