<?php
include_once(ROOT_PATH . "/app/controllers/DBOperations.php");
/**
 * This adapter translates code in our app to a format valid to store to and retrieve from a JSON file.
 * @author Albert Garcia
 */
class JSONAdapterController implements DBOperations
{

    private $usersFile = ROOT_PATH . "/web/json/users.json";
    private $tasksFile = ROOT_PATH . "/web/json/tasks.json";
    private $currentUser = null;

    /**
     * Inserts a new task at the end of the tasks json files. To do so, it loads all entries as an array, adds the new 
     * task as an array at the end from the parameter, encodes to json and writes all contents to the task file again.
     * @param array task data fetched from the create task form view, namely the task content ("task") and user who submitted it ("name")
     * @author Albert Garcia
     */
    public function insertTask($task)
    {
        try {
            $allTasks = $this->loadAllTasks();
            $lastId = $allTasks[count($allTasks)]["id"]; //fetch last id from the tasks file
            $newTask["id"] = $lastId + 1; //Add 1 to last id
            $creationTime = new DateTime(); //Current timestamp
            $newTask["timestampStart"] = $creationTime->format("l, j/M/y H:i"); //Creation timestamp, format like "Wedneday, 3/Nov/21 18:45"
            $newTask["timestampEnd"] = "Pending"; //Newly created, can't have a finished time yet
            $newTask["task"] = $task["task"]; //Contents of the task
            $newTask["name"] = $task["name"]; //User who created the task
            $newTask["status"] = "Pending"; //Newly created, pending by default
            array_push($allTasks, $newTask);
            $encodedTasks = json_encode($allTasks);
            file_put_contents($this->tasksFile, $encodedTasks);
            $_SESSION["tasks"] = $this->loadAllTasks();//Refresh the tasks overview upon insertion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca creada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant la creació de la tasca.\n" . $e . "\n"];
        }
    }

    /**
     * Edit the contents of an existing task. Either the task, the status or both can be changed at any
     * given time. Loads all tasks as an array, looks for the task with the same id as the parameter, and if any data is different,
     * it changes it, to then save all tasks back again on file.
     * @param int taskId - id of the task to change
     * @param string task - Contents of the task text
     * @param string status - Status of the task
     * @author Albert Garcia && Joan Vila Valls
     */
    public function editTask($taskId, $task, $status)
    {
        try {
            $allTasks = $this->loadAllTasks();

            $allTasks[$taskId]["name"]=$task;
            $allTasks[$taskId]["status"]=$status;

            if($status == "Finished"){
                $creationTime = new DateTime();
                $allTasks[$taskId]["timestampEnd"] = $creationTime->format("l, j/M/y H:i");
            }

            $encodedTasks = json_encode($allTasks);
            file_put_contents($this->tasksFile, $encodedTasks);
            $_SESSION["tasks"] = $this->loadAllTasks();//Refresh the tasks overview upon insertion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca modificada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'edició de la tasca.\n" . $e . "\n"];
        }
    }

    /**
     * Deletes the selected task from the file based on its id passed by parameter.
     * Loads all tasks, searches for the task with the id of the one to be erased, 
     * takes it out of the array, and writes the rest of tasks to file.
     * @param int taskId - the ID of the task to delete
     */
    public function deleteTask($taskId)
    {
        try {
            $allTasks = $this->loadAllTasks();
            $taskToDelete = array_search($taskId, array_column($allTasks, 'id')); //Find the correct task
            $key = array_keys($allTasks); //Get the name of the key, as it's a number
            $index = $key[$taskToDelete]; //Set the actual index that is going to be deleted
            unset($allTasks[$index]); //Delete
            //TODO: Might be useful to reform the indexes of the array
            $encodedTasks = json_encode($allTasks);
            file_put_contents($this->tasksFile, $encodedTasks);
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon deletion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca eliminada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'eliminació de la tasca.\n" . $e . "\n"];
        }
    }

    /**
     * Es pot buscar per autor o per contingut del nom de tasca, o simplement per estat, 
     * el qual sempre formarà part de la cerca
     * @author Joan Vila Valls
     */
    public function findTask(string $text, string $name, string $status):array
    {
        $flag1 = false;
        $flag2 = false;
        $tasksFound = array();

        $allTasksArr=$this->loadAllTasks(); 

        foreach ($allTasksArr as $element){

            foreach($element as $key => $value){

                if($key == "task"){//si hi ha una paraula de la cerca dins el titol aixeco una bandera 
                    $flag1 = ($this->compareStringWords($value,$text));
                }
                if($key == "name"){
                    $flag2 = ($this->compareStringWords($value,$name)) ;
                }
                if($key == "status"){
                    
                    if($value == $status)
                    {
                        
                        if(empty($text) and empty($name)){
                            array_push($tasksFound,$element);
                        } else if(empty($text) and $flag2){
                            array_push($tasksFound,$element);
                        } else if (empty($name) and $flag1){
                            array_push($tasksFound,$element);
                        } else if ($flag1 and $flag2){
                            array_push($tasksFound,$element);
                        }
                    }  
                }
            }
        }
        return $tasksFound;        
    }

    /**
     * Return true if any word matches
     * Case insensitive
     * Words are separated by space, semicolon, coma or dots.
     * Any special character is just part of the word
     * @author Joan Vila Valls
     */
    public function compareStringWords(string $s1,string $s2):bool
    {
        $s1=preg_split("/[\s,;\.]+/",$s1);
        $s2=preg_split("/[\s,;\.]+/",$s2); 
        foreach($s1 as $word){
            foreach($s2 as $cmpWord){
                if(strcasecmp($word,$cmpWord) == 0) {
                    return true;
                } 
            }
        }
        return false;
    }

    /**
     * It receives the username and password as an array $userData.
     * Converts it to JSON to look up in the web/json/users.json file if the user exists and whether his password is correct.
     * If it exists, returns a user object, if not, returns false.
     * @param array $userData has username in index 0, password in index 1
     * @return object boolean true if user exists, false if not
     * @author Albert Garcia
     */
    public function checkLoginData($userData)
    {

        $user = ["user" => $userData[0], "password" => $userData[1]];

        $allUsersStr = file_get_contents($this->usersFile);
        $allUsersArr = json_decode($allUsersStr, true);

        foreach ($allUsersArr as $userRecord) {
            if ($userRecord["user"] == $user["user"] && $userRecord["password"] == $user["password"]) {
                $this->currentUser = $userRecord;
                return true;
            }
        }

        return false;
    }

    /**
     * Loads all tasks on json file. Returns them as an associative array.
     * @return array Array of tasks.
     */
    public function loadAllTasks()
    {
        $allTasksStr = file_get_contents($this->tasksFile);
        $allTasksArr = json_decode($allTasksStr, true);
        return $allTasksArr;
    }

    /**
     * After checking login details, if method is successful it assigns the valid user to $currentUser property.
     * This is a getter for that property.
     * For JSON format, it is expected to receive an associative array where the key is the user ID, and the values 
     * are an array with user, password, and name keys and its values. Used to create a new user instance for the app.
     * @return array [userId => [user=>xxx, password=>xxx, name=>xxx]]
     */
    public function retrieveUserData()
    {
        return $this->currentUser;
    }
}
