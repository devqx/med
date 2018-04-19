<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/2/17
 * Time: 5:13 PM
 */
class PatientProcedureItem implements JsonSerializable
{
	private $id;
	private $serviceCenter;
	private $group;
	private $generic;
	private $item;
	private $batch;
	private $quantity;
	private $procedure;

	/**
	 * PatientProcedureItem constructor.
	 * @param $id
	 */
	public function __construct($id = null)
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
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
	}

	/**
	 * @return mixed
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param mixed $group
	 */
	public function setGroup($group)
	{
		$this->group = $group;
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
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
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
	 */
	public function setItem($item)
	{
		$this->item = $item;
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
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;
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
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}

	/**
	 * @return mixed
	 */
	public function getProcedure()
	{
		return $this->procedure;
	}

	/**
	 * @param mixed $procedure
	 * @return PatientProcedureItem
	 */
	public function setProcedure($procedure)
	{
		$this->procedure = $procedure;
		return $this;
	}

	

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


}