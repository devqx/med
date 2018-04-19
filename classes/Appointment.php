<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Appointment
 *
 * @author pauldic
 */
class Appointment implements JsonSerializable{
    private $id;
    private $group;
    private $startTime;
    private $endTime;
    private $attendedTime;
    private $status;
    private $editor;
    
    private $count;
    private $ids;

    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getGroup() {
        return $this->group;
    }

    public function getStartTime() {
        return $this->startTime;
    }

    public function getEndTime() {
        return $this->endTime;
    }

    public function getAttendedTime() {
        return $this->attendedTime;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCount() {
        return $this->count;
    }

    public function getEditor() {
        return $this->editor;
    }

    public function getIds() {
        return $this->ids;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    public function setStartTime($startTime) {
        $this->startTime = $startTime;
    }

    public function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    public function setAttendedTime($attendedTime) {
        $this->attendedTime = $attendedTime;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setCount($count) {
        $this->count = $count;
    }

    public function setEditor($editor) {
        $this->editor = $editor;
    }

    public function setIds($ids) {
        $this->ids = $ids;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}
