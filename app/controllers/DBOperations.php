<?php
/**
 * This interface declares the behaviours needed to interact with the DBs.
 */
interface DBOperations {
    public function insertTask();
    public function editTask();
    public function deleteTask();
    public function findTask();
    public function checkLoginData($userData);
    public function retrieveUserData();
}


?>