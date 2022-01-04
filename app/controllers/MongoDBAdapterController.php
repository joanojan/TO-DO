<?php
include_once(ROOT_PATH . "/app/controllers/DBOperations.php");
require_once(ROOT_PATH . "/vendor/autoload.php");
require_once(ROOT_PATH . "/app/models/Task.class.php");
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

    /**
     * Adapter constructor for Mongo DB operations.
     * @param string connectionURI - Admits a string that can be used in future to connect to a remote cluster
     * @author Albert Garcia
     */
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
            echo "Unable to connect to MongoDB\n" . $e->getMessage();
        }
    }

    /**
     * Inserts a new document into the tasks collection of the mongo db.
     * Receives the data for the task via the $task argument, and uses its information to write the new document.
     * @param array task data fetched from the create task form view, namely the task content ("task") and user who submitted it ("name")
     * @author Albert Garcia
     */
    public function insertTask($task)
    {
        try {
            $creationTime = new DateTime(); //Current timestamp
            $newTask["timestampStart"] = $creationTime->format("l, j M y H:i"); //Creation timestamp, format like "Wedneday, 3 Nov 21 18:45"
            $newTask["timestampEnd"] = "Pending"; //Newly created, can't have a finished time yet
            $newTask["task"] = $task["task"]; //Contents of the task
            $newTask["name"] = $task["name"]; //User who created the task
            $newTask["status"] = "Pending"; //Newly created, pending by default

            $this->taskCol->insertOne(
                [
                    "task" => $newTask["task"],
                    "name" => $newTask["name"],
                    "timestampStart" => $newTask["timestampStart"],
                    "timestampEnd" => $newTask["timestampEnd"],
                    "status" => $newTask["status"],
                ]
            );

            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon insertion to avoid showing the latest change
            if (count($_SESSION["tasks"]) == 1) { //Prevent showing an error message when inserting first task ever
                unset($_SESSION["errors"]);
            }
            $_SESSION["messages"] = ["Tasca creada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant la creació de la tasca.\n" . $e->getMessage() . "\n"];
        }
    }

    /**
     * Allows to edit a task in the mongo database, task collection. Gets a taskId by parameter and
     * the new task and status. If the status changes to finish, it takes the timestamp of the moment and adds it
     * to the document. Shows messages to the user in the end.
     * @param int taskId - id of the task to change
     * @param string task - Contents of the task text
     * @param string status - Status of the task
     * @author Albert Garcia
     */
    public function editTask($taskId, $task, $status)
    {
        try {
            $finishedTime = "Pending";
            if ($status == "Finished") {
                $creationTime = new DateTime();
                $finishedTime = $creationTime->format("l, j M y H:i");
            }
            $this->taskCol->updateOne(
                ['_id' => new MongoDB\BSON\ObjectID($taskId)],
                ['$set' => ['task' => $task, 'status' => $status, 'timestampEnd' => $finishedTime]]
            );

            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon insertion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca modificada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'edició de la tasca.\n" . $e->getMessage() . "\n"];
        }
    }

    /**
     * Deletes the document with the matching id in the mongo task collection.
     * @param int taskId - the ID of the task to delete
     * @author Albert Garcia
     */
    public function deleteTask($taskId)
    {
        try {
            $this->taskCol->deleteOne(
                ['_id' => new MongoDB\BSON\ObjectID($taskId)]
            );
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon deletion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca eliminada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'eliminació de la tasca.\n" . $e->getMessage() . "\n"];
        }
    }

    /**
     * Retrieve a certain task.
     * A task can be queried by its body ($text), its author($name), its status ($status), a combination of either,
     * or just the status.
     * or if we give it an $id instead, it will return that object within an array.
     * @param string text - body of the task
     * @param string name - author of the task
     * @param string status - status of task
     * @param string id - retrieve a specific task using its id on mongo db
     * @author Albert Garcia
     * @return array with task(s)
     */
    public function findTask(string $text, string $name, string $status, ?string $id = null): array
    {
        $foundTasks = [];
        try {
            $find = null;
            if ($id != null) { //We want to find a particular task by its id
                $find = $this->taskCol->find(['_id' => new MongoDB\BSON\ObjectID($id)]);
            } else {
                //Set the filters for the search, case insensitive
                $searchFields = [
                    'task' => ['$regex' => $text, '$options' => 'i'],
                    'name' => ['$regex' => $name, '$options' => 'i'],
                    'status' => ['$regex' => $status, '$options' => 'i'],
                ];
                //Take out fields not used for this search
                if ($text == "") {
                    unset($searchFields['task']);
                }
                if ($name == "") {
                    unset($searchFields['name']);
                }
                //Status will always have a value

                $find = $this->taskCol->find($searchFields); //Get a cursor with all matching documents

            }
            //Prepare all results as a Task object
            foreach ($find as $task) {
                $newTask = new Task($task);
                array_push($foundTasks, $newTask);
            }
        } catch (Exception $e) {
            echo "Unable to find this task.\n" . $e->getMessage();
        }
        return $foundTasks;
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
     * @author Albert Garcia
     */
    public function checkLoginData($userData)
    {

        $user = ["user" => $userData[0], "password" => $userData[1]];

        try {
            $userRecord = $this->userCol->findOne(['user' => $user["user"], 'password' => $user["password"]]);
            if ($userRecord != null) {
                $this->currentUser = $userRecord;
                return true;
            }
            throw new Exception;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Loads all tasks on mongo tasks collection. Returns them to the app as an associative array.
     * @return array Array of tasks.
     * @author Albert Garcia
     */
    public function loadAllTasks()
    {
        $results = $this->taskCol->find();
        $allTasksArr = [];
        foreach ($results as $task) {
            $newTask = new Task($task);
            array_push($allTasksArr, $newTask);
        }
        return $allTasksArr;
    }

    /**
     * After checking login details, if method is successful it assigns the valid user to $currentUser property.
     * This is a getter for that property.
     * It is expected to receive an associative array where the key is the user ID, and the values 
     * are an array with user, password, and name keys and its values. Used to create a new user instance for the app.
     * @return array [userId => [user=>xxx, password=>xxx, name=>xxx]]
     * @author Albert Garcia
     */
    public function retrieveUserData()
    {
        return $this->currentUser;
    }
}
