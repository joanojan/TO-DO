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
    } else {
        //Redirect to login with error. Erase all post variables and logout any users for safety
        unset($_POST);
        $controller->processLogout();
        $_SESSION["errors"] = array("Dades de login incorrectes per accedir a aquesta pàgina, identifica't.");
        header('Location: ' . WEB_ROOT . '/login');
    }
} else if (isset($_POST["logout"])){//User wants to log out
    $controller->processLogout();
    $_SESSION["messages"] = ["Sessió tancada correctament. Fins aviat!"];
    header('Location: ' . WEB_ROOT . '/login');
} else if(isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] != $_COOKIE['PHPSESSID'] || !isset($_SESSION["loggedUser"])) { //The logged user session is different than the current session or no one is logged in
    $controller->processLogout();
    $_SESSION["errors"] = array("La sessió ha caducat o hi ha hagut algun altre tipus d'error d'identificació.");
    header('Location: ' . WEB_ROOT . '/login');
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rock+3D&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Overpass&family=Rock+3D&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    'rock3d': ['"Rock 3D"'],//logo
                    'overpass':['Overpass', 'sans-serif'],//task text
                    'icons': ['"Material Icons"'],
                }
            }
        }
    </script>
</head>

<body class="bg-[url('../web/images/david-travis-5bYxXawHOQg-unsplash.jpg')] bg-cover bg-blend-saturation">
    <header class="grid grid-cols-8">
        <div class="bg-yellow-600 text-white p-4 col-span-6 text-xl flex items-center shrink">
            <h1 class="text-sm">
                <span class="font-rock3d text-3xl">TO-DO</span>  by A&J v0.5
            </h1>
        </div>

        <div class="bg-yellow-500 text-white p-4 flex place-items-center justify-center shrink-0 min-w-fit ">
        <span class="font-icons text-3xl m-2">face</span>    
        <h1> Hola, <br> <?php echo $_SESSION["loggedUser"]["name"]; ?></h1>
        </div>

        <div class="bg-yellow-400 text-white p-4 flex place-items-center justify-center shrink-0 min-w-fit">
            <form action="" method="post">
                <button type="submit" name="logout" class="border rounded-md bg-red-500 hover:bg-red-600 hover:shadow-md text-white text-justify align-center p-2 m-2 flex flex-nowrap flex-auto items-center justify-around text-sm">
                    <span class="font-icons text-xl m-2">power_settings_new</span>Tancar sessió</button>
            </form>
        </div>
    </header>