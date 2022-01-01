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
    <link rel="icon" type="image/x-icon" href="/web/images/favicon/favicon.ico">
    <link rel="shortcut icon" href="/web/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/web/images/favicon/favicon-32x32.png" type="image/png">
    <link rel="icon" type="image/x-icon" href="/web/images/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/web/images/favicon/favicon-32x32.png">
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
                },
                keyframes: {
                    'infocard-in': {
                        '0%': {transform: 'translateX(200%)'},
                        '20%': {transform: 'translateX(0%)'},
                        '80%': {transform: 'translateX(0%)', opacity: '1'},
                        '100%': {opacity: '0'},
                    },
                },
                animation: {
                    'infocard-in': 'infocard-in 3s ease-in-out forwards',
                }
            }
        }
    </script>
</head>

<body class="overflow-x-hidden overflow-y-auto md:bg-[url('../web/images/david-travis-5bYxXawHOQg-unsplash.jpg')] bg-[url('../web/images/hannah-olinger-8eSrC43qdro-unsplash.jpg')] backdrop-saturate-50 bg-cover bg-blend-saturation bg-no-repeat">
    <!--Header-->
    <header class="flex flex-nowrap">
        <div class="bg-yellow-600 text-white p-4 py-2 lg:w-[80%] md:w-[60%] w-[50%] text-xl flex items-center shrink">
            <h1 class="text-sm pl-2 ml-2">
                <span class="font-rock3d text-4xl">TO-DO</span> by A&J v0.5
            </h1>
        </div>
        <div class="bg-yellow-600 text-white p-4 py-2 flex place-items-center justify-center lg:w-[10%] md:w-[20%] w-25%">
            <span class="font-icons text-3xl m-2">face</span>
            <h1><?php echo $_SESSION["loggedUser"]["name"]; ?></h1>
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
    <div class="<?= $hide ?> animate-infocard-in fixed m-4 p-4 w-fit max-w-1/2 lg:max-w-[25%] right-0 top-[10%] bg-white/95 rounded-md shadow shadow-xl flex flex-auto justify-items-center">
        <p class="text-green-900 align-middle mx-auto text-center md:text-justify lg:text-left">
            <?php
            if (isset($_SESSION["messages"])) {
                foreach ($_SESSION["messages"] as $message) {
                    echo $message;
                    echo '<span class="font-icons text-2xl ml-2 mr-2 align-middle">
                    sentiment_very_satisfied
                    </span>';
                }
                if (isset($_SESSION["loggedUser"])) { //Erase messages only if a user is logged in
                    unset($_SESSION["messages"]);
                }
            }
            ?></p>
        <p class="text-red-900 align-middle mx-auto text-center md:text-justify lg:text-left">
            <?php
            if (isset($_SESSION["errors"])) {
                foreach ($_SESSION["errors"] as $message) {
                    echo $message;
                    echo '<span class="font-icons text-2xl ml-2 mr-2 align-middle">
                    mood_bad
                    </span>';
                }
                if (isset($_SESSION["loggedUser"])) { //Erase messages only if a user is logged in
                    unset($_SESSION["errors"]);
                }
            }
            ?></p>
    </div>