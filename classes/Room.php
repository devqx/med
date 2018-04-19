<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Room
 *
 * @author pauldic
 */
class Room implements JsonSerializable{
    private $id;
    private $name;
    private $ward;
    private $roomType;
    

    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getWard() {
        return $this->ward;
    }

    public function setWard($ward) {
        $this->ward = $ward;
    }

    public function getRoomType() {
        return $this->roomType;
    }

    public function setRoomType($roomType) {
        $this->roomType = $roomType;
    }

    
            
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}

?>
