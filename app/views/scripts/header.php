<?php
//Start general app controller
$controller = new ApplicationController();
//Check login status
$controller->checkLoginStatus();

//Are there any errors or messages to display?
$hide = "hidden";
if (isset($_SESSION["messages"]) || isset($_SESSION["errors"])) {
    $hide = "";
}
unset($_POST);
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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    'rock3d': ['"Rock 3D"'], //logo
                    'overpass': ['Overpass', 'sans-serif'], //task text
                    'icons': ['"Material Icons"'], //icons
                }
            }
        }
    </script>
</head>

<body class="md:bg-[url('../web/images/david-travis-5bYxXawHOQg-unsplash.jpg')] bg-[url('../web/images/hannah-olinger-8eSrC43qdro-unsplash.jpg')] backdrop-saturate-50 bg-cover bg-blend-saturation bg-no-repeat">
    <!--Header-->
    <header class="flex flex-nowrap">
        <div class="bg-yellow-600 text-white p-4 py-2 lg:w-[80%] md:w-[60%] w-[50%] text-xl flex items-center shrink">
            <h1 class="text-sm pl-2 ml-2">
                <span class="font-rock3d text-4xl">TO-DO</span> by A&J v0.5
            </h1>
        </div>
        <div class="bg-yellow-600 text-white p-4 py-2 flex place-items-center justify-center lg:w-[10%] md:w-[20%] w-25%">
            <span class="font-icons text-3xl m-2">face</span>
            <h1><?php echo $_SESSION["loggedUser"]["name"];?></h1>
        </div>

        <div class="bg-gradient-to-r from-yellow-600 via-transparent to-transparent text-white p-4 py-2 flex place-items-center justify-center shrink-0 lg:w-[10%] md:w-[20%] w-[25%]">
            <form action="" method="post">
                <button type="submit" name="logout" class="hidden md:flex rounded-lg bg-red-600 hover:bg-red-700 hover:shadow-inner text-white text-justify align-center p-2 py-0.5 m-0 flex-nowrap flex-auto items-center justify-around text-sm">
                    <span class="font-icons text-xl m-2">logout</span>Sortir</button>
                    <!--Mobile-->
                    <button type="submit" name="logout" class="md:hidden rounded-lg bg-red-600 hover:bg-red-700 hover:shadow-inner text-white text-justify align-center p-2 py-0.5 m-0 flex flex-nowrap flex-auto items-center justify-around text-sm">
                    <span class="font-icons text-xl m-2">logout</span></button>
            </form>
        </div>
    </header>
    <!--End header-->
     <!--Messages to the user-->
    <div class="<?= $hide ?> m-4 p-4 w-1/3 h-[7%] absolute inset-x-0 top-0 opacity-40 bg-white/80 rounded-md shadow shadow-lg mx-auto border border-white">
    </div>
    <div class="<?= $hide ?> m-4 p-2 w-1/3 h-[7%] absolute inset-x-0 top-0  mx-auto flex flex-auto flex-nowrap place-items-center">
        <p class="text-green-900 align-middle">
            <?php
            if (isset($_SESSION["messages"])) {
                foreach ($_SESSION["messages"] as $message) {
                    echo '<span class="font-icons text-3xl ml-2 mr-2 align-middle">
                    sentiment_very_satisfied
                    </span>';
                    echo $message;
                }
                if (isset($_SESSION["loggedUser"])) { //Erase messages only if a user is logged in
                    unset($_SESSION["messages"]);
                }
            }
            ?></p>
        <p class="text-red-900">
            <?php
            if (isset($_SESSION["errors"])) {
                foreach ($_SESSION["errors"] as $message) {
                    echo '<span class="font-icons text-3xl ml-2 mr-2 align-middle">
                    mood_bad
                    </span>';
                    echo $message;
                }
                if (isset($_SESSION["loggedUser"])) { //Erase messages only if a user is logged in
                    unset($_SESSION["errors"]);
                }
            }
            ?></p>
    </div>