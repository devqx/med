<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplate
 *
 * @author pauldic
 */
class OphthalmologyTemplate implements JsonSerializable{
    private $id;
    private $label;
    
    private $data;
    
    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    function getId() {
        return $this->id;
    }

    function getLabel() {
        return $this->label;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setLabel($label) {
        $this->label = $label;
    }
    function getData() {
        return $this->data;
    }

    function setData($data) {
        $this->data = $data;
    }
    
    public function __toString() {
        return "".$this->label;
    }

    
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
