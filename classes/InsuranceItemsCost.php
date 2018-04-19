<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceItemsCost
 *
 * @author pauldic
 */
class InsuranceItemsCost implements JsonSerializable{
    private $id;
    private $item;
    private $sellingPrice;
    private $insuranceScheme;
    private $clinic;
    private $defaultPrice;
    private $followUpPrice;
    private $theatrePrice;
    private $surgeonPrice;
    private $anesthesiaPrice;
    private $coPay;
    private $serviceGroup;
    private $type;
    private $capitated;
    private $insuranceCode;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     * @return InsuranceItemsCost
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     * @return InsuranceItemsCost
     */
    public function setItem($item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSellingPrice()
    {
        return $this->sellingPrice;
    }

    /**
     * @param mixed $sellingPrice
     * @return InsuranceItemsCost
     */
    public function setSellingPrice($sellingPrice)
    {
        $this->sellingPrice = $sellingPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInsuranceScheme()
    {
        return $this->insuranceScheme;
    }

    /**
     * @param mixed $insuranceScheme
     * @return InsuranceItemsCost
     */
    public function setInsuranceScheme($insuranceScheme)
    {
        $this->insuranceScheme = $insuranceScheme;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClinic()
    {
        return $this->clinic;
    }

    /**
     * @param mixed $clinic
     * @return InsuranceItemsCost
     */
    public function setClinic($clinic)
    {
        $this->clinic = $clinic;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultPrice()
    {
        return $this->defaultPrice;
    }

    /**
     * @param mixed $defaultPrice
     * @return InsuranceItemsCost
     */
    public function setDefaultPrice($defaultPrice)
    {
        $this->defaultPrice = $defaultPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFollowUpPrice()
    {
        return $this->followUpPrice;
    }

    /**
     * @param mixed $followUpPrice
     * @return InsuranceItemsCost
     */
    public function setFollowUpPrice($followUpPrice)
    {
        $this->followUpPrice = $followUpPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTheatrePrice()
    {
        return $this->theatrePrice;
    }

    /**
     * @param mixed $theatrePrice
     * @return InsuranceItemsCost
     */
    public function setTheatrePrice($theatrePrice)
    {
        $this->theatrePrice = $theatrePrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSurgeonPrice()
    {
        return $this->surgeonPrice;
    }

    /**
     * @param mixed $surgeonPrice
     * @return InsuranceItemsCost
     */
    public function setSurgeonPrice($surgeonPrice)
    {
        $this->surgeonPrice = $surgeonPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnesthesiaPrice()
    {
        return $this->anesthesiaPrice;
    }

    /**
     * @param mixed $anesthesiaPrice
     * @return InsuranceItemsCost
     */
    public function setAnesthesiaPrice($anesthesiaPrice)
    {
        $this->anesthesiaPrice = $anesthesiaPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoPay()
    {
        return $this->coPay;
    }

    /**
     * @param mixed $coPay
     * @return InsuranceItemsCost
     */
    public function setCoPay($coPay)
    {
        $this->coPay = $coPay;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceGroup()
    {
        return $this->serviceGroup;
    }

    /**
     * @param mixed $serviceGroup
     * @return InsuranceItemsCost
     */
    public function setServiceGroup($serviceGroup)
    {
        $this->serviceGroup = $serviceGroup;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return InsuranceItemsCost
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapitated()
    {
        return $this->capitated;
    }

    /**
     * @param mixed $capitated
     * @return InsuranceItemsCost
     */
    public function setCapitated($capitated)
    {
        $this->capitated = $capitated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInsuranceCode()
    {
        return $this->insuranceCode;
    }

    /**
     * @param mixed $insuranceCode
     * @return InsuranceItemsCost
     */
    public function setInsuranceCode($insuranceCode)
    {
        $this->insuranceCode = $insuranceCode;
        return $this;
    }
    
    
}
