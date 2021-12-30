<?php
/**
 * This interface declares the behaviours needed to interact with the DBs.
 */
interface DBOperations {
    public function insertTask($task);
    public function editTask($taskId, $task, $status);
    public function deleteTask($taskId);
    public function findTask(string $text);
    public function checkLoginData($userData);
    public function retrieveUserData();
    public function loadAllTasks();
}


?>