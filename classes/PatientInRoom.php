<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientInRoom
 *
 * @author pauldic
 */
class PatientInRoom  implements JsonSerializable{
    private $id;
    private $patient;
    private $queueFor;
    private $timeIn;
    

    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($roomID) {
        $this->id = $roomID;
    }

    public function getPatient() {
        return $this->patient;
    }

    public function setPatient($patient) {
        $this->patient = $patient;
    }

    public function getQueueFor() {
        return $this->queueFor;
    }

    public function setQueueFor($queue_for) {
        $this->queueFor = $queue_for;
    }

    public function getTimeIn() {
        return $this->timeIn;
    }

    public function setTimeIn($time_in) {
        $this->timeIn = $time_in;
    }


    
        
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
    
}

?>
