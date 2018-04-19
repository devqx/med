<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LifeStyle
 *
 * @author pauldic
 */
class LifeStyle implements JsonSerializable{
    private $id;
    private $title;
    private $description;
    private $ids;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getIds() {
        return $this->ids;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setIds($ids) {
        $this->ids = $ids;
    }

        public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
