<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplateData
 *
 * @author pauldic
 */
class OphthalmologyTemplateData implements JsonSerializable{
    private $id;
    private $label;
    private $ophthalmologyTemplate;
    private $reference;
    
    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    function getId() {
        return $this->id;
    }

    function getLabel() {
        return $this->label;
    }

    function getOphthalmologyTemplate() {
        return $this->ophthalmologyTemplate;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setLabel($label) {
        $this->label = $label;
    }

    function setOphthalmologyTemplate($ophthalmologyTemplate) {
        $this->ophthalmologyTemplate = $ophthalmologyTemplate;
    }

    function getReference() {
        return $this->reference;
    }

    function setReference($reference) {
        $this->reference = $reference;
    }

        
    public function __toString() {
        return "".$this->label;
    }

    
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
