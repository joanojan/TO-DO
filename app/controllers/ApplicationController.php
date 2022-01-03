<?php

include_once(ROOT_PATH . '/app/controllers/DBConnectionController.php');
include_once(ROOT_PATH . '/app/models/User.class.php');
/**
 * Base controller for the application.
 * Add general things in this controller.
 */
class ApplicationController extends Controller
{
    private $user; //TODO create user class

    /**
     * Login button on login view calls this function. It receives the username and password as an array $userData.
     * This data is used to look up in the database if the user exists. If it exists, returns true, if not, returns false.
     * @param array $userData has username in index 0, password in index 1 //In future we can add DB type to array to pass it dynamically depending on the strategy to use
     * @return boolean true if user exists, false if not
     * @author Albert Garcia
     */
    public function processLogin($userData)
    {

        $conn = $this->connect(); //connects to JSON DB by now. This variable will have access to all methods within the JSONAdapter class

        if ($conn->checkLoginData($userData)) {
            $this->user = new User($conn->retrieveUserData()); //User class should be able to get all data from here and instantiate the user in the constructor
            return true;
        } else {
            return false;
        }
    }

    /**
     * User getter for this controller.
     * @return User object
     */
    public function getUser()
    {
        return $this->user;
    }

    public function processLogout()
    {
        $this->user = null;
        unset($_POST["logout"]);
        unset($_SESSION);
        unset($_COOKIE);
        session_destroy();
        session_start();
    }

    /**
     * Method to be run everytime a user tries to access a login only part of the app, 
     * run it in the header before anything else.
     */
    public function checkLoginStatus()
    {
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
        } else if (isset($_POST["logout"])) { //User wants to log out
            $this->processLogout();
            $_SESSION["messages"] = ["Sessió tancada correctament. Fins aviat!"];
            header('Location: ' . WEB_ROOT . '/login');
        } else if (isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] != $_COOKIE['PHPSESSID'] || !isset($_SESSION["loggedUser"])) { //The logged user session is different than the current session or no one is logged in
            $this->processLogout();
            $_SESSION["errors"] = array("La sessió ha caducat o hi ha hagut algun altre tipus d'error d'identificació.");
            header('Location: ' . WEB_ROOT . '/login');
        } else if (isset($_SESSION["loggedUser"]) && $_SESSION["loggedUser"]['session'] == $_COOKIE['PHPSESSID']) { //User is already logged in. Should be unset upon "log out" button push.
            //Access grantes, proceed to execute the rest of this file.
        }
    }

    /**
     * Returns a connection to appropriate database.
     * TODO: once the rest of databases are implemented, refactor to admit a database by parameter upon login for the rest
     * of the session.
     */
    public function connect()
    {
        return DBConnectionController::getInstance("JSON");
    }

    /**
     * Returns an error message when no tasks are stored in the database.
     * @author Albert Garcia
     */
    public function showEmptyTasksMsg()
    {
        if ((!isset($_SESSION["tasks"]) || count($_SESSION["tasks"]) < 1) && !isset($_POST["search"])) {
            $_SESSION["errors"] = ["No hi ha cap tasca encara..."];
        }
    }

    /**
     * Render the requested tasks only. They should be stored in $_SESSION["tasks"] 
     */
    public function renderTasks()
    {

        foreach ($_SESSION["tasks"] as $task) {
            //Render statuses dynamically
            $color = "red";
            $icon = "flag";
            $finish = "";
            if ($task["status"] == "In progress") {
                $icon = "schedule";
                $color = "amber";
            } else if ($task["status"] == "Finished") {
                $color = "green";
                $icon = "task_alt";
                $finish = '<p class="lg:ml-4 lg:m-0 lg:pl-4 lg:pr-4 pl-1 pr-1 my-auto"><span class="font-icons text-lg mr-0.5 align-middle">schedule</span> ' . $task["timestampEnd"] . '</p>';
            }
?>
            <!--Task card-->
            <div class="bg-white/50 border border-2 rounded-md border-<?= $color; ?>-700 shadow shadow-md shadow-<?= $color; ?>-700 rounded-md m-2 p-3 flex md:flex-nowrap flex-wrap justify-around align-items-center">
                <div class="w-full">
                    <!--Task-->
                    <div class="flex flex-nowrap flex-auto items-center justify-left">
                        <span class="font-icons text-2xl p-2">notes</span>
                        <p class="whitespace-normal font-overpass text-xl"><?= $task["task"]; ?></p>
                    </div>
                    <!--Author and start date-->
                    <div class="flex flex-nowrap flex-auto items-center justify-left mb-1">
                        <span class="font-icons text-2xl p-2">face</span>
                        <p class="whitespace-normal inline"><b><?= $task["name"]; ?></b>, <?= $task["timestampStart"]; ?></p>
                    </div>
                </div>
                <div class="md:w-[30%] w-full ml-2">
                    <!--Status-->
                    <div class="flex lg:flex-nowrap py-1 lg:py-0 flex-wrap flex-auto align-items-center justify-center lg:justify-left rounded-md bg-<?= $color ?>-700 text-white text-center lg:text-left">
                        <span class="font-icons text-2xl p-2 my-auto"><?= $icon ?></span>
                        <p class="whitespace-normal my-auto pr-1 pl-1"><?= $task["status"] ?></p>
                        <?= $finish; ?>
                    </div>
                    <!--Action buttons-->
                    <div id="buttons" class="flex lg:flex-nowrap flex-wrap flex-auto align-items-center justify-items-around w-full">
                        <!--Edit-->
                        <form action="<?= WEB_ROOT . "/process"; ?>" method="post" class="w-full md:mr-1 shrink">
                            <input type="hidden" name="edit-<?= $task["id"] ?>">
                            <button type="submit" name="editTask" class="inline align-middle rounded-md p-2 w-full mt-2 bg-yellow-600 hover:bg-gray-600 hover:inner-shadow text-white flex flex-nowrap flex-auto align-items-center justify-center
                            <?php if (isset($_SESSION["editingTask"])) {
                                echo "hidden";
                            } ?>"><span class="font-icons pl-2 pr-2 align-middle">edit</span>Edit</button>
                        </form>
                        <!--Delete-->
                        <form action="<?= WEB_ROOT . "/process"; ?>" method="post" class="w-full shrink">
                            <!--           <input type="hidden" name="delete-<?= $task["id"] ?>">-->
                            <button type="submit" name="deleteTask" value="<?= $task["id"] ?>" class="inline align-middle rounded-md p-2 mt-2 w-full bg-red-600 hover:bg-red-800 hover:inner-shadow hover:animate-pulse text-white flex flex-nowrap flex-auto align-items-center justify-center"><span class="font-icons pl-2 pr-2 align-middle">delete</span>Delete</button>
                        </form>
                    </div>
                </div>
            </div>
<?php
        }
    }
}
