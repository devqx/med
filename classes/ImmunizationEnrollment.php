<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImmunizationEnrollment
 *
 * @author pauldic
 */
class ImmunizationEnrollment implements JsonSerializable {

    private $patient;
    private $enrolledAt;
    private $enrolledOn;
    private $enrolledBy;

    function __construct($patient=NULL) {
        $this->patient = $patient;
    }

    function getPatient() {
        return $this->patient;
    }

    function getEnrolledAt() {
        return $this->enrolledAt;
    }

    function getEnrolledOn() {
        return $this->enrolledOn;
    }

    function getEnrolledBy() {
        return $this->enrolledBy;
    }

    function setPatient($patient) {
        $this->patient = $patient;
    }

    function setEnrolledAt($enrolledAt) {
        $this->enrolledAt = $enrolledAt;
    }

    function setEnrolledOn($enrolledOn) {
        $this->enrolledOn = $enrolledOn;
    }

    function setEnrolledBy($enrolledBy) {
        $this->enrolledBy = $enrolledBy;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
