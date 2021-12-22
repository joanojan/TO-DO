<?php
include_once(ROOT_PATH . "/app/controllers/DBOperations.php");
/**
 * This adapter translates code in our app to a format valid to store to and retrieve from a JSON file.
 * @author Albert Garcia
 */
class JSONAdapterController implements DBOperations {

    private $usersFile = ROOT_PATH. "/web/json/users.json";
    private $tasksFile = ROOT_PATH . "/web/json/tasks.json";
    private $currentUser = null;

    //private $readUsersFile = fopen($this->usersFile, "r") or die ("Problem opening users database.");
   // private $readTasksFile = fopen($this->tasksFile, "r") or die ("Problem opening tasks database.");

    public function insertTask(){}
    public function editTask(){}
    public function deleteTask(){}
    public function findTask(){}

    /**
     * It receives the username and password as an array $userData.
     * Converts it to JSON to look up in the web/json/users.json file if the user exists and whether his password is correct.
     * If it exists, returns a user object, if not, returns false.
     * @param array $userData has username in index 0, password in index 1
     * @return object boolean true if user exists, false if not
     * @author Albert Garcia
     */
    public function checkLoginData($userData){

        $user = ["user"=>$userData[0], "password"=>$userData[1]];

        $allUsersStr = file_get_contents($this->usersFile);
        $allUsersArr = json_decode($allUsersStr, true);        

        foreach($allUsersArr as $userRecord){
            if ($userRecord["user"] == $user["user"] && $userRecord["password"] == $user["password"]){
                $this->currentUser = $userRecord;
                return true;
            }
        }
        
        return false;

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




?>