<?php

include_once(ROOT_PATH . '/app/controllers/DBConnectionController.php');
include_once(ROOT_PATH . '/app/models/User.class.php');
/**
 * Base controller for the application.
 * Add general things in this controller.
 */
class ApplicationController extends Controller 
{
    private $user;//TODO create user class

    /**
     * Login button on login view calls this function. It receives the username and password as an array $userData.
     * This data is used to look up in the database if the user exists. If it exists, returns true, if not, returns false.
     * @param array $userData has username in index 0, password in index 1 //In future we can add DB type to array to pass it dynamically depending on the strategy to use
     * @return boolean true if user exists, false if not
     * @author Albert Garcia
     */
    public function processLogin($userData){
        
        $conn = DBConnectionController::getInstance("JSON");//connects to JSON DB by now. This variable will have access to all methods within the JSONAdapter class
        
        if($conn->checkLoginData($userData)){
            $this->user = new User($conn->retrieveUserData());//User class should be able to get all data from here and instantiate the user in the constructor
            return true;
        } else {
            return false;
        }
    }

    /**
     * User getter for this controller.
     * @return User object
     */
    public function getUser(){
        return $this->user;
    }
	
}
