<?php

include_once(ROOT_PATH . '/app/controllers/ApplicationController.php');
//Start general app controller
$controller = new ApplicationController();

//Read the data sent by the form on /login/index
$userData[0] = htmlspecialchars($_POST["username"]);
$userData[1] = htmlspecialchars($_POST["password"]);

if($controller->processLogin($userData)){
    //TODO redirect to or render point of entry
    echo "Login amb èxit. Benvingut/da " . $controller->getUser()->getName() . PHP_EOL;
} else {
   //TODO redirect to login with error
   echo "Usuari/Contrassenya incorrectes";
}


?>