<?php

class Signup {
    private $error = "";

    public function evaluate($data) {
        foreach($data as $key => $value) {
            if(empty($value)) {
                $this->error = $this->error . $key . " Champ vide !<br>"; 
            }
        }
        if($this->error == "") {
        // pas d'erreur
            $this->create_user($data);
        } else {
         
            return $this->error;
        }
        
   }

   public function create_user($data) {
       $first_name = $data['first_name'];
       $last_name = $data['last_name'];
       $genre = $data['genre'];
       $email = $data['email'];
       $log = $data['log'];
       $password = $data['password'];
       // a créer

 
      
        $query = "insert into users (fname, lname, genre, mail, passwd, login) 
            value ('$first_name', '$last_name', '$genre', '$email', '$password', '$log')";

        $db = new Database();
        $db->save($query);
    }





}