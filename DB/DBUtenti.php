<?php

class DBUtenti
{ 	private $connection;
       public function __construct()
    {
        //Setup della connessione col DB
        $db = new DBConnectionManager();
        $this->connection = $db->runConnection();
    }

?>