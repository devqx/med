<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/9/14
 * Time: 12:56 PM
 */

class NurseReport implements JsonSerializable{

    private $id;
    private $patient;
    private $date;
    private $type;
    private $scheme;
    private $count;
    private $meta;

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }



    public function setScheme($scheme){
        $this->scheme = $scheme;
    }

    public function getScheme(){
        return $this->scheme;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function getType(){
        return $this->type;
    }

    public function setDate($date){
        $this->date = $date;
    }

    public function getDate(){
        return $this->date;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setPatient($patient){
        $this->patient = $patient;
    }

    public function getPatient(){
        return $this->patient;
    }

    public function getCount(){
        return $this->count;
    }

    public function setCount($count){
        $this->count = $count;
    }

    public function jsonSerialize(){
        return (object)get_object_vars($this);
    }

    function __toString(){
        return $this->patient->getFullname()."'s report object";
    }
}