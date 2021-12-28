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

    public function processLogout(){
        $this->user = null;
        unset($_POST["logout"]);
        unset($_SESSION);
        unset($_COOKIE);
        session_destroy();
        session_start();
    }

    /**
     * Function to be run everytime a user tries to access a login only part of the app, run it in the header before anything else.
     */
    public function checkLoginStatus(){
        if (isset($_POST["process-login"])) { //Attempting login
            //Recover data sent by post
            $usuari_contrasenya = new Request();
            $valors_post = $usuari_contrasenya->getAllParams();
            //Read the data sent by the form on /login/index and clean it up
            $userData[0] = htmlspecialchars($valors_post['username']);
            $userData[1] = htmlspecialchars($valors_post['password']);
            if ($this->processLogin($userData)) {
                //Correct user logged in. Save it into the session for use in the app as an array with user id, username and name.
                $_SESSION["loggedUser"]['session'] = $_COOKIE['PHPSESSID'];
                $_SESSION["loggedUser"]['id'] = $this->getUser()->getId();
                $_SESSION["loggedUser"]['username'] = $this->getUser()->getUser();
                $_SESSION["loggedUser"]['name'] = $this->getUser()->getName();
            } else {
                //Redirect to login with error. Erase all post variables and logout any users for safety
                unset($_POST);
                $this->processLogout();
                $_SESSION["errors"] = array("Dades de login incorrectes per accedir a aquesta pàgina, identifica't.");
                header('Location: ' . WEB_ROOT . '/login');
            }
        } else if (isset($_POST["logout"])){//User wants to log out
            $this->processLogout();
            $_SESSION["messages"] = ["Sessió tancada correctament. Fins aviat!"];
            header('Location: ' . WEB_ROOT . '/login');
        } else if(isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] != $_COOKIE['PHPSESSID'] || !isset($_SESSION["loggedUser"])) { //The logged user session is different than the current session or no one is logged in
            $this->processLogout();
            $_SESSION["errors"] = array("La sessió ha caducat o hi ha hagut algun altre tipus d'error d'identificació.");
            header('Location: ' . WEB_ROOT . '/login');
        } else if (isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] == $_COOKIE['PHPSESSID']) { //User is already logged in. Should be unset upon "log out" button push.
            //Access grantes, proceed to execute the rest of this file.
        }
    }
}
