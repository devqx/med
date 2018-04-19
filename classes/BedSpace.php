<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BedSpace
 *
 * @author pauldic
 */
class BedSpace implements JsonSerializable{
    private $id;
    private $bedName;
    private $bedtype;
    private $room;
    private $available;
    private $description;
    private $hosp;
    
    function __construct() {
        
    }
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getBedName() {
        return $this->bedName;
    }

    public function setBedName($bedName) {
        $this->bedName = $bedName;
    }

    public function getBedtype() {
        return $this->bedtype;
    }

    public function setBedtype($bedtype) {
        $this->bedtype = $bedtype;
    }

    public function getRoom() {
        return $this->room;
    }

    public function setRoom($room) {
        $this->room = $room;
    }

    public function getAvailable() {
        return $this->available;
    }

    public function setAvailable($available) {
        $this->available = $available;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getHosp() {
        return $this->hosp;
    }

    public function setHosp($hosp) {
        $this->hosp = $hosp;
    }

        
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}

?>
