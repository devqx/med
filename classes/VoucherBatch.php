<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/20/15
 * Time: 10:50 AM
 */

class VoucherBatch implements JsonSerializable {
    private $id;
    private $quantity;
    private $used;
    private $amount;
    private $type;
    private $generator;
    private $description;
    private $dateGenerated;
    private $expirationDate;
    private $serviceCentre;

    function __construct($id=NULL){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getQuantity(){
        return $this->quantity;
    }

    public function setQuantity($quantity){
        $this->quantity = $quantity;
    }

    public function getUsed(){
        return $this->used;
    }

    public function setUsed($used){
        $this->used = $used;
    }

    public function getAmount(){
        return $this->amount;
    }

    public function setAmount($amount){
        $this->amount = $amount;
    }

    public function getType(){
        return $this->type;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function getGenerator(){
        return $this->generator;
    }

    public function setGenerator($generator){
        $this->generator = $generator;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    public function getDateGenerated(){
        return $this->dateGenerated;
    }

    public function setDateGenerated($dateGenerated){
        $this->dateGenerated = $dateGenerated;
    }

    public function getExpirationDate(){
        return $this->expirationDate;
    }

    public function setExpirationDate($expirationDate){
        $this->expirationDate = $expirationDate;
    }

    public function getServiceCentre(){
        return $this->serviceCentre;
    }

    public function setServiceCentre($serviceCentre){
        $this->serviceCentre = $serviceCentre;
    }

    function jsonSerialize(){
        return (object) get_object_vars($this);
    }
}