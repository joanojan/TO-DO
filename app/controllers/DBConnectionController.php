<?php
include_once(ROOT_PATH . '/app/controllers/JSONAdapterController.php');
/**
 * Database connection singleton. Receives which type of DB we want to use for the session.
 * @author Albert Garcia
 */
class DBConnectionController {
    private static $connection = null;
    private $db;

    private function __construct(){
        $this->db = null;
    }

    public static function getInstance(string $database){
        if(DBConnectionController::$connection == null){
            DBConnectionController::$connection = new DBConnectionController();
        }
        if ($database == "JSON"){
            DBConnectionController::$connection->db = new JSONAdapterController();
        }
        return DBConnectionController::$connection->db;
    }
    
}



?>