<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Block
 *
 * @author pauldic
 */
class Block implements JsonSerializable{
    private $id;
    private $name;
    private $description;
    private $hospital;

    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getHospital() {
        return $this->hospital;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setHospital($hospital) {
        $this->hospital = $hospital;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
