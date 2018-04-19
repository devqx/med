<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VaccineBooster
 *
 * @author pauldic
 */
class VaccineBooster implements JsonSerializable{
    private $id;
    private $interval;
    private $intervalScale;
    private $startAge;
    private $startAgeScale;
    private $vaccine;


    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getInterval() {
        return $this->interval;
    }

    public function getIntervalScale() {
        return $this->intervalScale;
    }

    public function getStartAge() {
        return $this->startAge;
    }

    public function getStartAgeScale(){
        return $this->startAgeScale;
    }

    public function getVaccine() {
        return $this->vaccine;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setInterval($interval) {
        $this->interval = $interval;
    }

    public function setIntervalScale($intervalScale) {
        $this->intervalScale = $intervalScale;
    }

    public function setStartAge($startAge) {
        $this->startAge = $startAge;
    }

    public function setStartAgeScale($startAgeScale){
        $this->startAgeScale = $startAgeScale;
    }

    public function setVaccine($vaccine) {
        $this->vaccine = $vaccine;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);        
    }

}
