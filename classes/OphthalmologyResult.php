<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabResult
 *
 * @author pauldic
 */
class OphthalmologyResult implements JsonSerializable{
    private $id;
    private $ophthalmologyTemplate;
    private $patientOphthalmology;
    private $abnormalValue;
    private $approved;
    private $approvedBy;
    private $approvedDate;
    
    
    private $data;
    
    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    function getId() {
        return $this->id;
    }

    function getOphthalmologyTemplate() {
        return $this->ophthalmologyTemplate;
    }

    function getPatientOphthalmology() {
        return $this->patientOphthalmology;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setOphthalmologyTemplate($ophthalmologyTemplate) {
        $this->ophthalmologyTemplate = $ophthalmologyTemplate;
    }

    function setPatientOphthalmology($ophthalmologyRequest) {
        $this->patientOphthalmology = $ophthalmologyRequest;
    }

    function getData() {
        return $this->data;
    }

    function setData($data) {
        $this->data = $data;
    }

    public function getAbnormalValue(){
        return $this->abnormalValue;
    }

    public function setAbnormalValue($abnormalValue){
        $this->abnormalValue = $abnormalValue;
    }

    function isApproved() {
        return $this->approved;
    }

    function getApprovedBy() {
        return $this->approvedBy;
    }

    function getApprovedDate() {
        return $this->approvedDate;
    }

    function setApproved($approved) {
        $this->approved =(bool) $approved;
    }

    function setApprovedBy($approvedBy) {
        $this->approvedBy = $approvedBy;
    }

    function setApprovedDate($approvedDate) {
        $this->approvedDate = $approvedDate;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
