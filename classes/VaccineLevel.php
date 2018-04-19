<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VaccineLevel
 *
 * @author pauldic
 */
class VaccineLevel implements JsonSerializable{
    private $id;
    private $vaccine;
    private $level;
    private $startIndex;
    private $endIndex;
    private $startAge;
    private $endAge;
    private $duration; //?
    private $ageScaleStart;
    private $ageScaleStop;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAgeScaleStart()
    {
        return $this->ageScaleStart;
    }

    /**
     * @param mixed $ageScaleStart
     */
    public function setAgeScaleStart($ageScaleStart)
    {
        $this->ageScaleStart = $ageScaleStart;
    }

    /**
     * @return mixed
     */
    public function getAgeScaleStop()
    {
        return $this->ageScaleStop;
    }

    /**
     * @param mixed $ageScaleStop
     */
    public function setAgeScaleStop($ageScaleStop)
    {
        $this->ageScaleStop = $ageScaleStop;
    }

    /**
     * @return mixed
     */
    public function getEndAge()
    {
        return $this->endAge;
    }

    /**
     * @param mixed $endAge
     */
    public function setEndAge($endAge)
    {
        $this->endAge = $endAge;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getVaccine() {
        return $this->vaccine;
    }

    public function setVaccine($vaccine) {
        $this->vaccine = $vaccine;
    }

    public function getLevel() {
        return $this->level;
    }

    public function setLevel($level) {
        $this->level = $level;
    }

    public function getStartIndex() {
        return $this->startIndex;
    }

    public function setStartIndex($startIndex) {
        $this->startIndex = $startIndex;
    }

    public function getEndIndex() {
        return $this->endIndex;
    }

    public function setEndIndex($endIndex) {
        $this->endIndex = $endIndex;
    }

    public function getStartAge() {
        return $this->startAge;
    }

    public function setStartAge($startAge) {
        $this->startAge = $startAge;
    }

    public function getDuration() {
        return $this->duration;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
    }


    
    public function jsonSerialize() {
        return (object) get_object_vars($this);        
    }
}
