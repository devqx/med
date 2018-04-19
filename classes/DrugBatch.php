<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/25/14
 * Time: 12:30 PM
 */
class DrugBatch implements JsonSerializable
{
	
	private $id;
	private $name;
	private $drug;
	private $quantity;
	private $expirationDate;
	private $serviceCentre;
	
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
	 * @return DrugBatch
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
	 * @return DrugBatch
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDrug()
	{
		return $this->drug;
	}
	
	/**
	 * @param mixed $drug
	 *
	 * @return DrugBatch
	 */
	public function setDrug($drug)
	{
		$this->drug = $drug;
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
	 * @return DrugBatch
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getExpirationDate()
	{
		return $this->expirationDate;
	}
	
	/**
	 * @param mixed $expirationDate
	 *
	 * @return DrugBatch
	 */
	public function setExpirationDate($expirationDate)
	{
		$this->expirationDate = $expirationDate;
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
	 *
	 * @return DrugBatch
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