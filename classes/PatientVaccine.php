<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientVaccine
 *
 * @author pauldic
 */
class PatientVaccine implements JsonSerializable{
    private $id;
    private $patient;
    private $vaccine;
    private $isBooster;
    private $vaccineLevel;
    private $dueDate;
    private $billed;
    private $entryDate;
    private $takenBy;
    private $takeType;
    private $internal;
    private $realAdministerDate;
    private $expirationDate;
    

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getPatient() {
        return $this->patient;
    }

    public function getVaccine() {
        return $this->vaccine;
    }

    public function getIsBooster() {
        return $this->isBooster;
    }

    public function getVaccineLevel() {
        return $this->vaccineLevel;
    }

    public function getDueDate() {
        return $this->dueDate;
    }

    public function getBilled() {
        return $this->billed;
    }

    public function getEntryDate() {
        return $this->entryDate;
    }

    public function getTakenBy() {
        return $this->takenBy;
    }

    public function getTakeType() {
        return $this->takeType;
    }

    public function getInternal() {
        return $this->internal;
    }

    public function getRealAdministerDate() {
        return $this->realAdministerDate;
    }

    public function getExpirationDate() {
        return $this->expirationDate;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPatient($patient) {
        $this->patient = $patient;
    }

    public function setVaccine($vaccine) {
        $this->vaccine = $vaccine;
    }

    public function setIsBooster($isBooster) {
        $this->isBooster = $isBooster;
    }

    public function setVaccineLevel($vaccineLevel) {
        $this->vaccineLevel = $vaccineLevel;
    }

    public function setDueDate($dueDate) {
        $this->dueDate = $dueDate;
    }

    public function setBilled($paid) {
        $this->billed = $paid;
    }

    public function setEntryDate($entryDate) {
        $this->entryDate = $entryDate;
    }

    public function setTakenBy($takenBy) {
        $this->takenBy = $takenBy;
    }

    public function setTakeType($takeType) {
        $this->takeType = $takeType;
    }

    public function setInternal($internal) {
        $this->internal = $internal;
    }

    public function setRealAdministerDate($realAdministerDate) {
        $this->realAdministerDate = $realAdministerDate;
    }

    public function setExpirationDate($expirationDate) {
        $this->expirationDate = $expirationDate;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
