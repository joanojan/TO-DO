<?php
include_once(ROOT_PATH . '/app/controllers/JSONAdapterController.php');
include_once(ROOT_PATH . '/app/controllers/MySQLAdapterController.php');
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
        } else if ($database == "mysql") {
            //Creo la BD mysql i la inicialitzo si no existeix
            include_once(ROOT_PATH . "/config/createToDoMySQLDB.php");
            include_once(ROOT_PATH . "/config/db.inc.php");
            DBConnectionController::$connection->db = new MySQLAdapterController($dbh);
        } else if ($database == "mongodb"){
            //TODO
        }
        return DBConnectionController::$connection->db;
    }
    
}



?>