<?php
//Start general app controller
$controller = new ApplicationController();
//Check login status
$controller->checkLoginStatus();
/**
 * The buttons submit the data from the same page with form action=""
 * Now we must check the action to render the new content 
 * $_SESSION["tasks"]
 */

//Prevent flag conflict for refresh on edit mode
function unsetEditingTask(){
    if(isset($_SESSION["editingTask"])){
        unset($_SESSION["editingTask"]);
    }
}

//Searching for some task... 
if (isset($_POST["search"])){
    unsetEditingTask();//Prevent flag conflict for refresh on edit mode
    $_SESSION["tasks"] = $controller->connect()->findTask($_POST["task"],$_POST["name"],$_POST["status"]);
}
//Load all tasks?
if (isset($_POST["loadAllTasks"])) {
    unsetEditingTask();//Prevent flag conflict for refresh on edit mode
    $_SESSION["tasks"] = $controller->connect()->loadAllTasks();
}
//Insert new task?
if (isset($_POST["insertTask"])) {
    unsetEditingTask();//Prevent flag conflict for refresh on edit mode
    $task = [
        "task" => $_POST["task"],
        "name" => $_SESSION["loggedUser"]["name"]
    ];
    $controller->connect()->insertTask($task);
}
//Delete a task?
if (isset($_POST["deleteTask"])) {
    unsetEditingTask();//Prevent flag conflict for refresh on edit mode
    $arrayIndex = array_key_first($_POST); //Fetch the name of the element in the POST array, as it's dynamic
    $index = strpos($arrayIndex, "-") + 1; //Set the index where first number of id occurs
    $taskId = substr($arrayIndex, $index); //Extract the number
    $controller->connect()->deleteTask($taskId);
}
//Edit a task?
if (isset($_POST["editTask"])) {
    $_SESSION["tasks"] = $controller->connect()->loadAllTasks();//Prevent conflicts if a refresh is triggered on the edit mode
    $arrayIndex = array_key_first($_POST); //Fetch the name of the element in the POST array, as it's dynamic
    $index = strpos($arrayIndex, "-") + 1; //Set the index where first number of id occurs
    $taskId = substr($arrayIndex, $index); //Extract the number
    //Store the ID of task being edited
    $_SESSION["editingTask"] = $taskId;
    $_SESSION["tasks"] = [$_SESSION["tasks"][$taskId]];//Show only the task we are editing
    //End step in next view: $controller->connect()->editTask($taskId, $task, $status);
}
if(isset($_POST["confirmEdit"])){
    unset($_SESSION["editingTask"]);
    $controller->connect()->editTask($_SESSION["tasks"][0]["id"], $_POST["task"], $_POST["status"]);
}
//Are there any errors or messages to display?
$hide = "hidden";
if (isset($_SESSION["messages"]) || isset($_SESSION["errors"])) {
    $hide = "";
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