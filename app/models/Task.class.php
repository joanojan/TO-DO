<?php

class Task {
    private $id;
    private $timestampStart;
    private $timestampEnd;
    private $task;
    private $name;
    private $status;

    public function __construct($taskData){
        $this->id = $taskData["id"];
        $this->timestampStart = $taskData["timestampStart"];
        $this->timestampEnd = $taskData["timestampEnd"];
        $this->task = $taskData["task"];
        $this->name = $taskData["name"];
        $this->status = $taskData["status"];
    }

    public function getId(){
        return $this->id;
    }
    public function getTimestampStart(){
        return $this->timestampStart;
    }
    public function getTimestampEnd(){
        return $this->timestampEnd;
    }
    public function getTask(){
        return $this->task;
    }
    public function getName(){
        return $this->name;
    }
    public function getStatus(){
        return $this->status;
    }
    public function setStatus($newStatus){
        return $this->status = $newStatus;
    }
    
}

?>