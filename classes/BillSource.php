<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/4/15
 * Time: 11:55 AM
 */

class BillSource implements JsonSerializable {
    private $id;
    private $name;

    function __construct($id=NULL){
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id){
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name){
        $this->name = $name;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}