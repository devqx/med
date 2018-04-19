<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdmissionConfiguration
 *
 * @author pauldic
 */
class AdmissionConfiguration implements JsonSerializable{
    private $id;
    private $code;
    private $name;
    private $defaultPrice;

    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function getDefaultPrice() {
        return $this->defaultPrice;
    }

    public function setDefaultPrice($defaultPrice) {
        $this->defaultPrice = $defaultPrice;
    }

        
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
