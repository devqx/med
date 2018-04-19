<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Drug
 *
 * @author pauldic
 */
class Drug implements JsonSerializable
{
	private $id;
	private $name;
	private $code;
	private $generic;
	private $manufacturer;
	private $erpProduct;
	private $batches;
	private $stockUOM;
	private $basePrice;
	
	//    private $expiryDate;
	private $stockQuantity;
	
	function __construct($id = null)
	{
		$this->id = $id;
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
	 *
	 * @return Drug
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
	 * @return Drug
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return Drug
	 */
	public function setCode($code)
	{
		$this->code = $code;
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
	 * @return Drug
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getManufacturer()
	{
		return $this->manufacturer;
	}
	
	/**
	 * @param mixed $manufacturer
	 *
	 * @return Drug
	 */
	public function setManufacturer($manufacturer)
	{
		$this->manufacturer = $manufacturer;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getErpProduct()
	{
		return $this->erpProduct;
	}
	
	/**
	 * @param mixed $erpProduct
	 *
	 * @return Drug
	 */
	public function setErpProduct($erpProduct)
	{
		$this->erpProduct = $erpProduct;
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
	 *
	 * @return Drug
	 */
	public function setBatches($batches)
	{
		$this->batches = $batches;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getStockUOM()
	{
		return $this->stockUOM;
	}
	
	/**
	 * @param mixed $stockUOM
	 *
	 * @return Drug
	 */
	public function setStockUOM($stockUOM)
	{
		$this->stockUOM = $stockUOM;
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
	 * @return Drug
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getStockQuantity()
	{
		return $this->stockQuantity;
	}
	
	/**
	 * @param mixed $stockQuantity
	 *
	 * @return Drug
	 */
	public function setStockQuantity($stockQuantity)
	{
		$this->stockQuantity = $stockQuantity;
		return $this;
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
}