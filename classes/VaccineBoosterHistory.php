<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/5/15
 * Time: 4:44 PM
 */

class VaccineBoosterHistory implements JsonSerializable {
    private $id;
    private $patientVaccineBooster;
    private $dateTaken;
    private $takenBy;

    function __construct($id = NULL){
        $this->id = $id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setPatientVaccineBooster($patientVaccineBooster){
        $this->patientVaccineBooster = $patientVaccineBooster;
    }

    public function setDateTaken($dateTaken){
        $this->dateTaken = $dateTaken;
    }

    public function setTakenBy($takenBy){
        $this->takenBy = $takenBy;
    }

    public function getId(){
        return $this->id;
    }

    public function getPatientVaccineBooster(){
        return $this->patientVaccineBooster;
    }

    public function getDateTaken(){
        return $this->dateTaken;
    }

    public function getTakenBy(){
        return $this->takenBy;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}