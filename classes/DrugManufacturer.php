<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugManufacturer 
 *
 * @author pauldic
 */
class DrugManufacturer implements JsonSerializable{
    private $id;
    private $name;

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
            
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
