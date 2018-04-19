<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of State
 *
 * @author pauldic
 */
class State  implements JsonSerializable{
    private $id;
    private $name;
    private $lgas;
    

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getLgas() {
        return $this->lgas;
    }

    public function setLgas($lgas) {
        $this->lgas = $lgas;
    }

    
        
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}
