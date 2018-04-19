<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceBillableItem
 *
 * @author pauldic
 */
class InsuranceBillableItem implements JsonSerializable{
    private $id;
    private $item;
    private $itemDescription;
    private $itemGroupCategory;
    private $clinic;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getItem() {
        return $this->item;
    }

    public function getItemDescription() {
        return $this->itemDescription;
    }

    public function getItemGroupCategory() {
        return $this->itemGroupCategory;
    }

    public function getClinic() {
        return $this->clinic;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setItem($item) {
        $this->item = $item;
    }

    public function setItemDescription($itemDescription) {
        $this->itemDescription = $itemDescription;
    }

    public function setItemGroupCategory($itemGroupCategory) {
        $this->itemGroupCategory = $itemGroupCategory;
    }

    public function setClinic($clinic) {
        $this->clinic = $clinic;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
