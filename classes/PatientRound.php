<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotificationOptions
 *
 * @author pauldic
 */
class PatientRound  implements JsonSerializable{
    private $id;
    private $patientId;
    private $frequency;
    private $interval;
    private $computedFrequencyHour;
    private $totalRounds;
    private $nextRoundTime;
    private $state;
    
    function __construct(){
        
    }
//    function __construct($id, $patientId, $frequency, $interval, $computedFrequencyHour, $totalRounds, $nextRoundTime, $state) {
//        $this->id = $id;
//        $this->patientId = $patientId;
//        $this->frequency = $frequency;
//        $this->interval = $interval;
//        $this->computedFrequencyHour = $computedFrequencyHour;
//        $this->totalRounds = $totalRounds;
//        $this->nextRoundTime = $nextRoundTime;
//        $this->state = $state;
//    }
//    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPatient() {
        return $this->patientId;
    }

    public function setPatient($patientId) {
        $this->patientId = $patientId;
    }

    public function getFrequency() {
        return $this->frequency;
    }

    public function setFrequency($frequency) {
        $this->frequency = $frequency;
    }

    public function getInterval() {
        return $this->interval;
    }

    public function setInterval($interval) {
        $this->interval = $interval;
    }

    public function getComputedFrequencyHour() {
        return $this->computedFrequencyHour;
    }

    public function setComputedFrequencyHour($computedFrequencyHour) {
        $this->computedFrequencyHour = $computedFrequencyHour;
    }

    public function getTotalRounds() {
        return $this->totalRounds;
    }

    public function setTotalRounds($totalRounds) {
        $this->totalRounds = $totalRounds;
    }

    public function getNextRoundTime() {
        return $this->nextRoundTime;
    }

    public function setNextRoundTime($nextRoundTime) {
        $this->nextRoundTime = $nextRoundTime;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        $this->state = $state;
    }

    
        
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
//