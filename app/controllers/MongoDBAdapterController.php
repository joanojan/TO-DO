<?php
include_once(ROOT_PATH . "/app/controllers/DBOperations.php");
require_once(ROOT_PATH . "/vendor/autoload.php");
/**
 * This adapter translates code in our app to a format valid to store to and retrieve from a MongoDB database.
 * @author Albert Garcia
 */
class MongoDBAdapterController implements DBOperations
{
    private $currentUser = null;
    private $connectionURI = null;
    private $db = null;
    private $userCol = null;
    private $taskCol = null;

    public function __construct($connectionURI)
    {
        $this->connectionURI = $connectionURI; //Prepared for connection to actual cluster out of local dev
        try {
            $this->db = (new MongoDB\Client)->todo;
            $this->userCol = $this->db->users;
            if ($this->userCol->count() == 0) { //First run of the app, populate users for testing purposes
                $this->userCol->insertMany([
                    [
                        "id" => 1,
                        "user" => "jvila",
                        "password" => "jvila123",
                        "name" => "Joan Vila",
                    ],
                    [
                        "id" => 2,
                        "user" => "agarcia",
                        "password" => "agarcia123",
                        "name" => "Albert Garcia",
                    ],
                    [
                        "id" => 3,
                        "user" => "ralcalde",
                        "password" => "ralcalde123",
                        "name" => "Rubén Alcalde",
                    ],
                ]);
            }
            $this->taskCol = $this->db->tasks;
        } catch (Exception $e) {
            echo "Unable to connect to MongoDB\n" . $e;
        }
    }

    /**
     * @param array task data fetched from the create task form view, namely the task content ("task") and user who submitted it ("name")
     * @author
     */
    public function insertTask($task)
    {
        try {
            //TODO
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon insertion to avoid showing the latest change
            if (count($_SESSION["tasks"]) == 1) { //Prevent showing an error message when inserting first task ever
                unset($_SESSION["errors"]);
            }
            $_SESSION["messages"] = ["Tasca creada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant la creació de la tasca.\n" . $e . "\n"];
        }
    }

    /**
     * @param int taskId - id of the task to change
     * @param string task - Contents of the task text
     * @param string status - Status of the task
     * @author 
     */
    public function editTask($taskId, $task, $status)
    {
        try {
            //TODO
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon insertion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca modificada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'edició de la tasca.\n" . $e . "\n"];
        }
    }

    /**
     *
     * @param int taskId - the ID of the task to delete
     */
    public function deleteTask($taskId)
    {
        try {
            //TODO
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon deletion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca eliminada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'eliminació de la tasca.\n" . $e . "\n"];
        }
    }

    /**
     * Es pot buscar per autor o per contingut del nom de tasca, o simplement per estat, 
     * el qual sempre formarà part de la cerca
     * @author 
     */
    public function findTask(string $text, string $name, string $status): array
    {
        try {
            //TODO
        } catch (Exception $e) {
        }
        return array();
    }

    /**
     * Return true if any word matches
     * Case insensitive
     * Words are separated by space, semicolon, coma or dots.
     * Any special character is just part of the word
     * @author Joan Vila Valls
     */
    public function compareStringWords(string $s1, string $s2): bool
    {
        $s1 = preg_split("/[\s,;\.]+/", $s1);
        $s2 = preg_split("/[\s,;\.]+/", $s2);
        foreach ($s1 as $word) {
            foreach ($s2 as $cmpWord) {
                if (strcasecmp($word, $cmpWord) == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check the provided user data against the data stored on the users collection. If a username and password exist and match,
     * returns true as it is a valid user.
     * @param array $userData has username in index 0, password in index 1
     * @return object boolean true if user exists, false if not
     * @author 
     */
    public function checkLoginData($userData)
    {

        $user = ["user" => $userData[0], "password" => $userData[1]];

        try {
            $userRecord = $this->userCol->findOne(['user'=>$user["user"], 'password' => $user["password"]]);
            if($userRecord != null){
                $this->currentUser = $userRecord;
                return true;
            }
            throw new Exception;            
        } catch (Exception $e) {
            return false;
        }        
    }

    /**
     * Loads all tasks on json file. Returns them as an associative array.
     * @return array Array of tasks.
     * @author 
     */
    public function loadAllTasks()
    {
        //TODO
        return array();
    }

    /**
     * After checking login details, if method is successful it assigns the valid user to $currentUser property.
     * This is a getter for that property.
     * For JSON format, it is expected to receive an associative array where the key is the user ID, and the values 
     * are an array with user, password, and name keys and its values. Used to create a new user instance for the app.
     * @return array [userId => [user=>xxx, password=>xxx, name=>xxx]]
     * @author Albert Garcia
     */
    public function retrieveUserData()
    {
        return $this->currentUser;
    }
}
