<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientCareMember
 *
 * @author pauldic
 */
class PatientCareMember implements JsonSerializable {
    private $id;
    private $inPatient;
    private $careTeam;
    private $careMember;
    private $createBy;
    private $entryTime;
    private $status;
//    private $changedBy;
//    private $changeTime;
//    private $changeReason;
    private $type;
    private $primaryCare;
    private $primaryCareType;
    
    private $anId;


    function __construct($id=NULL) {
        $this->id = $id;
    }
  
    function getId() {
        return $this->id;
    }

    function getInPatient() {
        return $this->inPatient;
    }

    function getCareTeam() {
        return $this->careTeam;
    }

    function getCareMember() {
        return $this->careMember;
    }

    function getCreateBy() {
        return $this->createBy;
    }

    function getEntryTime() {
        return $this->entryTime;
    }

    function getStatus() {
        return $this->status;
    }

    function getType() {
        return $this->type;
    }

    function getPrimaryCare() {
        return $this->primaryCare;
    }

    function getPrimaryCareType() {
        return $this->primaryCareType;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setInPatient($inPatient) {
        $this->inPatient = $inPatient;
    }

    function setCareTeam($careTeam) {
        $this->careTeam = $careTeam;
    }

    function setCareMember($careMember) {
        $this->careMember = $careMember;
    }

    function setCreateBy($createBy) {
        $this->createBy = $createBy;
    }

    function setEntryTime($entryTime) {
        $this->entryTime = $entryTime;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setPrimaryCare($primaryCare) {
        $this->primaryCare = $primaryCare;
    }

    function setPrimaryCareType($primaryCareType) {
        $this->primaryCareType = $primaryCareType;
    }
    
    function getAnId() {
        return $this->anId;
    }

    function setAnId($anId) {
        $this->anId = $anId;
    }

            
    public function __toString() {
        return $this->type==="Staff"? $this->getCareMember():$this->getCareTeam();
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
