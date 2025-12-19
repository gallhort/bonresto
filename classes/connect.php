<?php

class Database {
    // Utiliser les variables d'environnement si présentes (fallback aux valeurs existantes)
    private $host = null;
    private $username = null;
    private $password = null;
    private $db = null;

    function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->username = getenv('DB_USER') ?: 'sam';
        $this->password = getenv('DB_PASS') ?: '123';
        $this->db = getenv('DB_NAME') ?: 'lebonresto';
    }

    function connect() {
        $cnn = mysqli_connect($this->host, $this->username, $this->password, $this->db);
        if ($cnn && !mysqli_set_charset($cnn, 'utf8mb4')) {
            // fallback silently
        }
        return $cnn;
    }

    function read($query) {
        $conn = $this->connect();
        $result = mysqli_query($conn, $query);

        if(!$result) {
            return false;
        } else {
            $data = false;
            while($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function save($query) {
        $conn = $this->connect();
        $result = mysqli_query($conn, $query);

        if(!$result) {
            echo('nok');
            return false;
        } else {
            echo('ok');
            return true;
        }
    }

    function check($query) {
        $conn = $this->connect();
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);

        if(!$result) {
       
            return false;
        } else {
         
            
           // echo($row['num_rows']);
            return $row['num_rows'];
        }
    }
}




