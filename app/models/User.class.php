<?php

class User {
    private $id;
    private $user;
    private $password;
    private $name;

    public function __construct($userData){
        $this->id = $userData["id"];
        $this->user = $userData["user"];
        $this->password = $userData["password"];
        $this->name = $userData["name"];
    }

    public function getId(){
        return $this->id;
    }
    public function getUser(){
        return $this->user;
    }
    public function getPassword(){
        return $this->password;
    }
    public function getName(){
        return $this->name;
    }
    
}

?>