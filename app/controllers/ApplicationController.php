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
     * Render the requested tasks only. They should be stored in $_SESSION["tasks"] 
     */
    public function renderTasks()
    {
        foreach ($_SESSION["tasks"] as $task) {
            //Render statuses dynamically
            $color = "red";
            $icon = "flag";
            $finish = "";
            if ($task["status"] == "In execution") {
                $icon = "schedule";
                $color = "amber";
            } else if ($task["status"] == "Finished") {
                $color = "green";
                $icon = "task_alt";
                $finish = '<p class="ml-2 whitespace-normal">on ' . $task["timestampEnd"] . '</p>';
            }
?>
            <!--Task card-->
            <div class="bg-white border border-2 rounded-md border-<?=$color;?>-700 shadow shadow-md shadow-<?=$color;?>-700 rounded-md m-2 p-3 grid grid-cols-2">
                <div>
                    <!--Task-->
                    <div class="flex flex-nowrap flex-auto items-center justify-left">
                        <span class="font-icons text-2xl p-2">notes</span>
                        <p class="whitespace-normal"><?= $task["task"]; ?></p>
                    </div>
                    <!--Author and start date-->
                    <div class="flex flex-nowrap flex-auto items-center justify-left">
                        <span class="font-icons text-2xl p-2">face</span>
                        <p class="whitespace-normal inline"><?= $task["name"]; ?> on <?= $task["timestampStart"]; ?></p>
                    </div>
                </div>
                <div>
                    <!--Status-->
                    <div class="flex flex-nowrap flex-auto items-center justify-left rounded-md bg-<?= $color ?>-700 text-white">
                        <span class="font-icons text-2xl p-2"><?= $icon ?></span>
                        <p class="whitespace-normal inline"><?= $task["status"] . $finish; ?></p>
                    </div>
                    <!--Action buttons-->
                    <div id="buttons" class="flex flex-nowrap flex-auto items-center justify-left">
                        <!--Edit-->
                        <form action="" method="post" class="mt-2 mb-2">
                            <input type="hidden" name="edit-<?= $task["id"] ?>">
                            <button type="submit" name="editTask" class="align-middle rounded-md p-2 mt-2  bg-yellow-600 hover:bg-gray-600 text-white flex flex-nowrap flex-auto items-center justify-around"><span class="font-icons pl-2 pr-2 align-middle">edit</span>Edit</button>
                        </form>
                        <!--Delete-->
                        <form action="" method="post" class="m-2">
                            <input type="hidden" name="delete-<?= $task["id"] ?>">
                            <button type="submit" name="deleteTask" class="align-middle rounded-md p-2 mt-2  bg-red-600 hover:bg-red-800 hover:animate-pulse text-white flex flex-nowrap flex-auto items-center justify-around"><span class="font-icons pl-2 pr-2 align-middle">delete</span>Delete</button>
                        </form>
                    </div>
                </div>
            </div>
<?php }
    }
}
