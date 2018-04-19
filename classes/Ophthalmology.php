<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Lab
 *
 * @author pauldic
 */
class Ophthalmology implements JsonSerializable{
    private $id;
    private $code;
    private $name;
    private $category;
    private $template;
    private $unitSymbol;
    private $reference;
    private $hospital;

    private $basePrice;

    private $description;
    
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

    public function getCategory() {
        return $this->category;
    }

    public function getUnitSymbol() {
        return $this->unitSymbol;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getHospital() {
        return $this->hospital;
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

    public function setCategory($category) {
        $this->category = $category;
    }
    function getTemplate() {
        return $this->template;
    }

    function setTemplate($template) {
        $this->template = $template;
    }

    public function setUnitSymbol($unitSymbol) {
        $this->unitSymbol = $unitSymbol;
    }

    public function setReference($reference) {
        $this->reference = $reference;
    }

    public function setHospital($hospital) {
        $this->hospital = $hospital;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param mixed $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}
