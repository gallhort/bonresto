<?php

class Database {
    private $host = "localhost";
    private $username = "sam";
    private $password = "123";
    private $db = "lebonresto";

    function connect() {
        $cnn = mysqli_connect($this->host, $this->username, $this->password, $this->db);
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




