<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/24/15
 * Time: 11:18 AM
 */

class ApprovedQueue implements JsonSerializable {
    private $id;
    private $patient;
    private $type;
    private $request;
    private $approvedTime;
    private $readStatus;

    function __construct($id=null){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getPatient(){
        return $this->patient;
    }

    public function setPatient($patient){
        $this->patient = $patient;
    }

    public function getType(){
        return $this->type;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function getRequest(){
        return $this->request;
    }

    public function setRequest($request){
        $this->request = $request;
    }

    public function getApprovedTime(){
        return $this->approvedTime;
    }

    public function setApprovedTime($approvedTime){
        $this->approvedTime = $approvedTime;
    }

    public function getReadStatus(){
        return $this->readStatus;
    }

    public function setReadStatus($readStatus){
        $this->readStatus = $readStatus;
    }

    function jsonSerialize(){
        return (object) get_object_vars($this);
    }
}