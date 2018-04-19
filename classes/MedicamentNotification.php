<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MedicamentNotification
 *
 * @author pauldic
 */
class MedicamentNotification implements JsonSerializable{
    private $id;
    private $admissionId;
    private $medicamentId;
    private $dueTime;
    private $attendedTime;
    private $status;
    

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getAdmissionId() {
        return $this->admissionId;
    }

    public function setAdmissionId($admissionId) {
        $this->admissionId = $admissionId;
    }

    public function getMedicamentId() {
        return $this->medicamentId;
    }

    public function setMedicamentId($medicamentId) {
        $this->medicamentId = $medicamentId;
    }

    public function getDueTime() {
        return $this->dueTime;
    }

    public function setDueTime($dueTime) {
        $this->dueTime = $dueTime;
    }

    public function getAttendedTime() {
        return $this->attendedTime;
    }

    public function setAttendedTime($attendedTime) {
        $this->attendedTime = $attendedTime;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

  
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}

?>
