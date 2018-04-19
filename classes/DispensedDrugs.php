<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DispensedDrugs
 *
 * @author pauldic
 */
class DispensedDrugs implements JsonSerializable
{
	private $id;
	private $drug;
	private $patient;
	private $quantity;
	private $billedTo;
	private $dispensedDate;
	private $pharmacist;
	private $serviceCenter;
	private $batch;
	private $quantityOverflow;
	private $type;

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
	 * @return DispensedDrugs
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return DispensedDrugs
	 */
	public function setDrug($drug)
	{
		$this->drug = $drug;
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
	 * @return DispensedDrugs
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return DispensedDrugs
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
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
	 * @return DispensedDrugs
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
	 * @return DispensedDrugs
	 */
	public function setDispensedDate($dispensedDate)
	{
		$this->dispensedDate = $dispensedDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPharmacist()
	{
		return $this->pharmacist;
	}

	/**
	 * @param mixed $pharmacist
	 * @return DispensedDrugs
	 */
	public function setPharmacist($pharmacist)
	{
		$this->pharmacist = $pharmacist;
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
	 * @return DispensedDrugs
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
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
	 * @return DispensedDrugs
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getQuantityOverflow()
	{
		return $this->quantityOverflow;
	}

	/**
	 * @param mixed $quantityOverflow
	 * @return DispensedDrugs
	 */
	public function setQuantityOverflow($quantityOverflow)
	{
		$this->quantityOverflow = $quantityOverflow;
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
	 * @return DispensedDrugs
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

}
