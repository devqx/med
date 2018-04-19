

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
class Bed implements JsonSerializable{
    private $id;
    private $name;
    private $room;
    private $available;
    private $description;
    private $defaultPrice;
    private $code;

    
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

    public function getRoom() {
        return $this->room;
    }

    public function setRoom($room) {
        $this->room = $room;
    }

    public function isAvailable() {
        return $this->available;
    }

    public function setAvailable($available) {
        $this->available = (bool)$available;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDefaultPrice() {
        return $this->defaultPrice;
    }

    public function setDefaultPrice($defaultPrice) {
        $this->defaultPrice = $defaultPrice;
    }

    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
    }

        
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}

?>
