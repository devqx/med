<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/22/18
 * Time: 10:28 PM
 */

class DispensedItems implements JsonSerializable
{
	
	private $id;
	private $item;
	private $quantity;
	private $batch;
	private $patient;
	private $billedTo;
	private $dispensedDate;
	private $type;
	private $unfiiledQuantity;
	private $serviceCenter;
	private $dispensedBy;
	
	/**
	 * DispensedItems constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
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
	 * @return DispensedItems
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
	 *
	 * @return DispensedItems
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
	 *
	 * @return DispensedItems
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBatch()
	{
		return $this->batch;
	}
	
	/**
	 * @param mixed $batch
	 *
	 * @return DispensedItems
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPatient()
	{
		return $this->patient;
	}
	
	/**
	 * @param mixed $patient
	 *
	 * @return DispensedItems
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBilledTo()
	{
		return $this->billedTo;
	}
	
	/**
	 * @param mixed $billedTo
	 *
	 * @return DispensedItems
	 */
	public function setBilledTo($billedTo)
	{
		$this->billedTo = $billedTo;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDispensedDate()
	{
		return $this->dispensedDate;
	}
	
	/**
	 * @param mixed $dispensedDate
	 *
	 * @return DispensedItems
	 */
	public function setDispensedDate($dispensedDate)
	{
		$this->dispensedDate = $dispensedDate;
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
	 *
	 * @return DispensedItems
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUnfiiledQuantity()
	{
		return $this->unfiiledQuantity;
	}
	
	/**
	 * @param mixed $unfiiledQuantity
	 *
	 * @return DispensedItems
	 */
	public function setUnfiiledQuantity($unfiiledQuantity)
	{
		$this->unfiiledQuantity = $unfiiledQuantity;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getServiceCenter()
	{
		return $this->serviceCenter;
	}
	
	/**
	 * @param mixed $serviceCenter
	 *
	 * @return DispensedItems
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDispensedBy()
	{
		return $this->dispensedBy;
	}
	
	/**
	 * @param mixed $dispensedBy
	 *
	 * @return DispensedItems
	 */
	public function setDispensedBy($dispensedBy)
	{
		$this->dispensedBy = $dispensedBy;
		return $this;
	}
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	
}