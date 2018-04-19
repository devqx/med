<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/23/15
 * Time: 10:26 AM
 */

class DistributionList implements JsonSerializable{
    private $id;
    private $name;
    private $sqlQuery;
    private $dateAdded;

    function __construct($id=null){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getSqlQuery(){
        return $this->sqlQuery;
    }

    public function setSqlQuery($sqlQuery){
        $this->sqlQuery = $sqlQuery;
    }

    public function getDateAdded(){
        return $this->dateAdded;
    }

    public function setDateAdded($dateAdded){
        $this->dateAdded = $dateAdded;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}