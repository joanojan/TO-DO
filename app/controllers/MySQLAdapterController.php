<?php
include_once(ROOT_PATH . "/app/controllers/DBOperations.php");

/**
 * This adapter translates code in our app to a format valid to store to and retrieve from a MySQL database.
 * @author Mostly done by me, myself and I, mr. JOAN VILA VALLS xd
 */
class MySQLAdapterController implements DBOperations
{
    private $currentUser = null;

    private $dbh;

    public function __construct(PDO $dbh) { 
        $this->dbh = $dbh;
    }

    /**
     * @param array task data fetched from the create task form view, namely the task content ("task") and user who submitted it ("name")
     * @author Myself, mr. Joan Vila Valls
     */
    public function insertTask($task)
    {
        try {
            $user_id=$_SESSION["loggedUser"]["id"];//sorry for this, $currentUser was null! Any idea?
            $sql = "INSERT INTO tasks (task, user_id) 
                    VALUES (:task, :user_id)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':task', $task["task"]);
            $stmt->bindParam(':user_id', $user_id);

            $stmt->execute();
            
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon insertion to avoid showing the latest change
            if (count($_SESSION["tasks"]) == 1) { //Prevent showing an error message when inserting first task ever
                unset($_SESSION["errors"]);
            }
            $_SESSION["messages"] = ["Tasca creada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant la creació de la tasca.\n"];
        }
    }

    /**
     * @param int taskId - id of the task to change
     * @param string task - Contents of the task text
     * @param string status - Status of the task
     * @author Joan Vila Valls
     */
    public function editTask($taskId, $task, $status)
    {
        try {

            $stmt = $this->dbh->prepare("UPDATE tasks SET task=?, status=? WHERE id = ?");
            $stmt->execute(array($task, $status, $taskId));
            
            if($status == "Finished"){
                $stmt = $this->dbh->prepare("UPDATE tasks SET timestampEnd=CURRENT_TIMESTAMP() WHERE id =?");
                $stmt->execute(array($taskId));
            } 
            $_SESSION["tasks"] = $this->loadAllTasks(); //Refresh the tasks overview upon insertion to avoid showing the latest change
            $_SESSION["messages"] = ["Tasca modificada correctament!\n"]; //Envia missatge per mostrar a la vista corresponent
        } catch (Exception $e) {
            $_SESSION["errors"] = ["S'ha produït un error durant l'edició de la tasca.\n"];
        }
    }

    /**
     *
     * @param int taskId - the ID of the task to delete
     */
    public function deleteTask($taskId)
    {
        try {

            $stmt = $this->dbh->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt -> execute(array($taskId));

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
    public function findTask(string $text, string $name, string $status): array
    {
        $flag1 = false;
        $flag2 = false;
        $tasksFound = array();
        
        $allTasksArr=$this->loadAllTasks(); 

        try { 
    
            foreach ($allTasksArr as $element){
    
                foreach($element as $key => $value){
                    if(!empty($value))
                        $value=strtolower($value);//per evitar errors si li passen majuscules

                    if($key == "task"){//si hi ha una paraula de la cerca dins el titol aixeco una bandera 
                        $flag1 = ($this->compareStringWords($value,$text));
                    }
                    if($key == "name"){//en el cas que hi hagi una coincidencia amb l'autor aixeco una altra bandera
                        $flag2 = ($this->compareStringWords($value,$name));
                    }
                    if($key == "status"){
                        if($value == $status)//si l'estat de la tasca coincideix amb l'estat que hom cerca
                        {
                            if(empty($text) and empty($name)){//si els altres criteris de cerca són buits
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
            if($tasksFound == []){
                unset($_SESSION["errors"]);
                $_SESSION["errors"] = ["No s'ha trobat cap tasca amb aquest criteri de cerca"];
            }
            return $tasksFound; 
            
        } catch (Exception $e) {
        }
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
     * 
     * @param array $userData has username in index 0, password in index 1
     * @return object boolean true if user exists, false if not
     * @author Joan Vila Valls
     */
    public function checkLoginData($userData)
    {
        try {

            $stmt = $this->dbh->prepare("select * from users where user = ? and password = ?");
            $stmt -> execute(array($userData[0], $userData[1]));
            $user = $stmt -> fetch(PDO::FETCH_ASSOC);

            if($user!=false) {
                $this->currentUser = $user;
                return true;
            }
            else return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * Loads all tasks on json file. Returns them as an associative array.
     * @return array Array of tasks.
     * @author Joan Vila Valls
     */
    public function loadAllTasks()
    {
        try {
            $stmt = $this->dbh->prepare("SELECT 
                                            tasks.id AS id,
                                            tasks.timestampStart AS timestampStart,
                                            tasks.timestampEnd AS timestampEnd,
                                            tasks.task AS task,
                                            users.name AS name,
                                            tasks.status AS status
                                        FROM
                                            tasks
                                                JOIN
                                            users ON tasks.user_id = users.id");
            $stmt -> execute();
            $allTasksArr = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            return $allTasksArr;

            } catch (Exception $e) {
                echo "Error al carregar les tasques: " . $e->getMessage();
            }
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
