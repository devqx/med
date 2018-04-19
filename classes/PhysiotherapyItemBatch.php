<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 2:07 PM
 */
class PhysiotherapyItemBatch implements JsonSerializable
{

    private $id;
    private $name;
    private $item;
    private $quantity;
    private $serviceCentre;

    /**
     * PhysiotherapyItemBatch constructor.
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
     * @return PhysiotherapyItemBatch
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return PhysiotherapyItemBatch
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return PhysiotherapyItemBatch
     */
    public function setItem($item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     * @return PhysiotherapyItemBatch
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceCentre()
    {
        return $this->serviceCentre;
    }

    /**
     * @param mixed $serviceCentre
     * @return PhysiotherapyItemBatch
     */
    public function setServiceCentre($serviceCentre)
    {
        $this->serviceCentre = $serviceCentre;
        return $this;
    }

    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}