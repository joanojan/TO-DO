<?php
//Start general app controller
$controller = new ApplicationController();
//Check login status
if (isset($_POST["process-login"])) { //Attempting login
    //Recover data sent by post
    $usuari_contrasenya = new Request();
    $valors_post = $usuari_contrasenya->getAllParams();
    //Read the data sent by the form on /login/index and clean it up
    $userData[0] = htmlspecialchars($valors_post['username']);
    $userData[1] = htmlspecialchars($valors_post['password']);
    if ($controller->processLogin($userData)) {
        //Correct user logged in. Save it into the session for use in the app as an array with user id, username and name.
        $_SESSION["loggedUser"]['session'] = $_COOKIE['PHPSESSID'];
        $_SESSION["loggedUser"]['id'] = $controller->getUser()->getId();
        $_SESSION["loggedUser"]['username'] = $controller->getUser()->getUser();
        $_SESSION["loggedUser"]['name'] = $controller->getUser()->getName();
        echo "<h1>Login amb èxit. Benvingut/da " . $_SESSION["loggedUser"]['name'] . "</h1>" . PHP_EOL;
    } else {
        //Redirect to login with error. Erase all post variables and logout any users for safety
        unset($_POST);
        $controller->processLogout();
        session_start();
        $_SESSION["errors"] = array("Dades de login incorrectes per accedir a aquesta pàgina, identifica't.");
        header('Location: ' . WEB_ROOT . '/login');
    }
} else if (isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] != $_COOKIE['PHPSESSID'] || isset($_POST["logout"])) { //The logged user session is different than the current session or user wants to log out
    $controller->processLogout();
} else if (isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] == $_COOKIE['PHPSESSID']) { //User is already logged in. Should be unset upon "log out" button push.
    //Proceed to execute the rest of this file.
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do by Albert&Joan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <header class="grid grid-cols-8">
        <div class="bg-yellow-600 text-white p-4 col-span-6 text-xl flex items-center shrink">
            <h1>
                TO-DO by A&J v0.5
            </h1>
        </div>

        <div class="bg-yellow-500 text-white p-4 flex place-items-center justify-center shrink-0 min-w-fit ">
            <h1> Hola, <?php echo "Joan Vila"; //$controller->getUser()->getName();
                        ?>
            </h1>
        </div>

        <div class="bg-yellow-400 text-white p-4 flex place-items-center justify-center shrink-0 min-w-fit">
            <input type="submit" name="logout" value="Tancar sessió" class="border rounded-md bg-red-500 text-white text-justify align-center p-2 m-2 place-self-center">
        </div>
    </header>