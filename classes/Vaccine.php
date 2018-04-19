<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vaccine
 *
 * @author pauldic
 */
class Vaccine implements JsonSerializable {
    private $id;
    private $code;
    private $name;
    private $description;
    private $price;
    private $levels;
    private $booster;
    private $hasBooster;
    private $active;
    public static $vaccineUpdateTypes = array(
        'n'=>'normal',
        'm'=>'migration',//this should not be selectable through the GUI
        'p'=>'patient-submitted'
    ); //according to the definition in the db: n=normal
    public static $routes = array(
        'im'=>'IntraMuscular',
        'sc'=>'SubCutaneous',
        'id'=>'IntraDermal',
        'in'=>'IntraNasal',
        'or'=>'Oral'
    );
    
    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getLevels() {
        return $this->levels;
    }

    public function getBooster() {
        return $this->booster;
    }

    public function getHasBooster() {
        return $this->hasBooster;
    }

    public function getActive(){
        return $this->active;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function setLevels($levels) {
        $this->levels = $levels;
    }

    public function setBooster($booster) {
        $this->booster = $booster;
    }

    public function setHasBooster($hasBooster) {
        $this->hasBooster = $hasBooster;
    }

    public function setActive($active){
        $this->active = (bool) $active;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}
