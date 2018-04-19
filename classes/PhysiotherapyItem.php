<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 11:52 AM
 */
class PhysiotherapyItem implements JsonSerializable
{
    private $id;
    private $code;
    private $name;
    private $basePrice;

    /**
     * PhysiotherapyItem constructor.
     * @param $id
     */
    public function __construct($id=NULL)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return PhysiotherapyItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return PhysiotherapyItem
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return PhysiotherapyItem
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return PhysiotherapyItem
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
        return $this;
    }


    function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }

}