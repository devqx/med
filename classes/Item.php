<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Item
 *
 * @author pauldic
 */
class Item implements JsonSerializable
{
	
	private $id;
	private $name;
	private $category;
	private $generic;
	private $code;
	private $description;
	private $basePrice;
	private $erpProductId;
	private $batches;
	private $quantity;
	private $data;
	
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 *
	 * @return Item
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
	 *
	 * @return Item
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}
	
	/**
	 * @param mixed $category
	 *
	 * @return Item
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getGeneric()
	{
		return $this->generic;
	}
	
	/**
	 * @param mixed $generic
	 *
	 * @return Item
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
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
	 *
	 * @return Item
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * @param mixed $description
	 *
	 * @return Item
	 */
	public function setDescription($description)
	{
		$this->description = $description;
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
	 *
	 * @return Item
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getErpProductId()
	{
		return $this->erpProductId;
	}
	
	/**
	 * @param mixed $erpProductId
	 *
	 * @return Item
	 */
	public function setErpProductId($erpProductId)
	{
		$this->erpProductId = $erpProductId;
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
	 *
	 * @return Item
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * @param mixed $data
	 *
	 * @return Item
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getBatches()
    {
        return $this->batches;
    }

    /**
     * @param mixed $batches
     * @return Item
     */
    public function setBatches($batches)
    {
        $this->batches = $batches;
        return $this;
    }

	
	public function __toString()
	{
		return $this->getName();
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
}
