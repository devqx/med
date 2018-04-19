<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/20/15
 * Time: 10:50 AM
 */

class Voucher implements JsonSerializable {
    private $id;
    private $batch;
    private $code;
    private $usedDate;
    private $voucherUser;

    function __construct($id=NULL){
        $this->id = $id;
    }

    function __toString(){
        return $this->getCode();
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getBatch(){
        return $this->batch;
    }

    public function setBatch($batch){
        $this->batch = $batch;
    }

    public function getCode(){
        return $this->code;
    }

    public function setCode($code){
        $this->code = $code;
    }

    public function getUsedDate(){
        return $this->usedDate;
    }

    public function setUsedDate($usedDate){
        $this->usedDate = $usedDate;
    }

    public function getVoucherUser(){
        return $this->voucherUser;
    }

    public function setVoucherUser($voucherUser){
        $this->voucherUser = $voucherUser;
    }

    function jsonSerialize(){
        return (object) get_object_vars($this);
    }
}