<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubscribedDoctor
 *
 * @author pauldic
 */
class SubscribedDoctor implements JsonSerializable{
    private $room;
    private $staff;
    private $specialization;
    private $time;
    private $onlineStatus;
    
    function __construct() {
        
    }

    public function getRoom() {
        return $this->room;
    }

    public function getStaff() {
        return $this->staff;
    }

    public function getSpecialization() {
        return $this->specialization;
    }

    public function getTime() {
        return $this->time;
    }

    public function getOnlineStatus() {
        return $this->onlineStatus;
    }

    public function setRoom($room) {
        $this->room = $room;
    }

    public function setStaff($staff) {
        $this->staff = $staff;
    }

    public function setSpecialization($specialization) {
        $this->specialization = $specialization;
    }

    public function setTime($time) {
        $this->time = $time;
    }

    public function setOnlineStatus($onlineStatus) {
        $this->onlineStatus = $onlineStatus;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
