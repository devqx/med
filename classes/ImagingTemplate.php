<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/29/15
 * Time: 12:56 PM
 */

class ImagingTemplate implements JsonSerializable {
    private $id;
    private $category;
    private $title;
    private $bodyPart;

    function __construct($id=NULL){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getCategory(){
        return $this->category;
    }

    public function setCategory($category){
        $this->category = $category;
    }

    public function getTitle(){
        return $this->title;
    }

    public function setTitle($title){
        $this->title = $title;
    }

    public function getBodyPart(){
        return $this->bodyPart;
    }

    public function setBodyPart($bodyPart){
        $this->bodyPart = $bodyPart;
    }

    function jsonSerialize(){
        return (object)get_object_vars($this);
    }
}