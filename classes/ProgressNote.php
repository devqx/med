<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProgressNote
 *
 * @author pauldic
 */
class ProgressNote implements JsonSerializable{
    private $id;
    private $inPatient;
    private $value;
    private $note;
    private $notedBy;
    private $entryTime;
    private $noteType;
    

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getInPatient() {
        return $this->inPatient;
    }

    public function getValue() {
        return $this->value;
    }

    public function getNote() {
        return $this->note;
    }

    public function getNotedBy() {
        return $this->notedBy;
    }

    public function getEntryTime() {
        return $this->entryTime;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setInPatient($inPatient) {
        $this->inPatient = $inPatient;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function setNote($note) {
        $this->note = $note;
    }

    public function setNotedBy($notedBy) {
        $this->notedBy = $notedBy;
    }

    public function setEntryTime($entryTime) {
        $this->entryTime = $entryTime;
    }

    /**
     * @return mixed
     */
    public function getNoteType()
    {
        return $this->noteType;
    }

    /**
     * @param mixed $noteType
     */
    public function setNoteType($noteType)
    {
        $this->noteType = $noteType;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
