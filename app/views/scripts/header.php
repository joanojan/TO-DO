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
//Load all tasks?
if (isset($_POST["loadAllTasks"])) {
    $_SESSION["tasks"] = $controller->connect()->loadAllTasks();
}
//Insert new task?
if (isset($_POST["insertTask"])) {
    $task = [
        "task" => $_POST["task"],
        "name" => $_SESSION["loggedUser"]["name"]
    ];
    $controller->connect()->insertTask($task);
}
//Finally, unset all POST requests
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

<body class="bg-[url('../web/images/david-travis-5bYxXawHOQg-unsplash.jpg')] bg-cover bg-blend-saturation">
    <header class="grid grid-cols-8">
        <div class="bg-yellow-600 text-white p-4 col-span-6 text-xl flex items-center shrink">
            <h1 class="text-sm pl-2 ml-2">
                <span class="font-rock3d text-4xl">TO-DO</span> by A&J v0.5
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
        <!--Messages to the user-->
    </header>
    <div class="<?= $hide ?> m-4 p-4 w-1/3 h-[7%] absolute inset-x-0 top-0 opacity-40 bg-white/40 rounded-md shadow shadow-lg shadow-white mx-auto border border-white">
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
                unset($_SESSION["messages"]);
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
                unset($_SESSION["errors"]);
            }
            ?></p>
    </div>