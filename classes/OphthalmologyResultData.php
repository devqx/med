<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabResultData
 *
 * @author pauldic
 */
class OphthalmologyResultData implements JsonSerializable{
    private $ophthalmologyResult;
    private $ophthalmologyTemplateData;
    private $value;
    
    function __construct($ophthalmologyResult=NULL, $ophthalmologyTemplateData=NULL) {
        $this->ophthalmologyResult = $ophthalmologyResult;
        $this->ophthalmologyTemplateData = $ophthalmologyTemplateData;
    }

    function getOphthalmologyResult() {
        return $this->ophthalmologyResult;
    }

    function getOphthalmologyTemplateData() {
        return $this->ophthalmologyTemplateData;
    }

    function getValue() {
        return $this->value;
    }

    function setOphthalmologyResult($ophthalmologyResult) {
        $this->ophthalmologyResult = $ophthalmologyResult;
    }

    function setOphthalmologyTemplateData($ophthalmologyTemplateData) {
        $this->ophthalmologyTemplateData = $ophthalmologyTemplateData;
    }

    function setValue($value) {
        $this->value = $value;
    }

    
    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
