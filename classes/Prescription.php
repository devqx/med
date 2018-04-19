<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Prescription
 *
 * @author pauldic
 */
class Prescription implements JsonSerializable
{
	
	private $id;
	private $external;
	private $patient;
	private $when;
	private $code;
	private $requestedBy;
	private $serviceCentre;
	private $inPatient;
	private $note;
	private $hospital;
	private $encounter;
	private $data;
	private $refilled_off;
	private $refill_number;
	private $refill_sate;
	private $refill_date;
	private $refillable;
	private $prescribed_by;
	
	
	function __construct($id = null)
	{
		$this->id = $id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefillSate()
	{
		return $this->refill_sate;
	}
	
	/**
	 * @param mixed $refill_sate
	 *
	 * @return Prescription
	 */
	public function setRefillSate($refill_sate)
	{
		$this->refill_sate = $refill_sate;
		return $this;
	}
	
	/**
	 * @param $refillable
	 *
	 * @return mixed
	 */
	public function setRefillable($refillable)
	{
		$this->refillable = $refillable;
		return $this;
	}
	
	public function getExternal()
	{
		return $this->external;
	}
	
	/**
	 * @param mixed $external
	 *
	 * @return Prescription
	 */
	public function setExternal($external)
	{
		$this->external = $external;
		return $this;
	}
	
	public function getPatient()
	{
		return $this->patient;
	}
	
	public function setPatient($patient)
	{
		$this->patient = $patient;
	}
	
	public function getWhen()
	{
		return $this->when;
	}
	
	public function setWhen($when)
	{
		$this->when = $when;
	}
	
	public function getCode()
	{
		return $this->code;
	}
	
	public function setCode($code)
	{
		$this->code = $code;
	}
	
	public function getRequestedBy()
	{
		return $this->requestedBy;
	}
	
	public function setRequestedBy($requestedBy)
	{
		$this->requestedBy = $requestedBy;
	}
	
	/**
	 * @return mixed
	 */
	
	public function getInPatient()
	{
		return $this->inPatient;
	}
	
	public function setInPatient($inpatient)
	{
		$this->inPatient = $inpatient;
	}
	
	public function getNote()
	{
		return $this->note;
	}
	
	public function setNote($note)
	{
		$this->note = $note;
	}
	
	public function getHospital()
	{
		return $this->hospital;
	}
	
	public function setHospital($hospital)
	{
		$this->hospital = $hospital;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function setData($data)
	{
		$this->data = $data;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefillNumber()
	{
		return $this->refill_number;
	}
	
	/**
	 * @param mixed $refill_number
	 *
	 * @return Prescription
	 */
	public function setRefillNumber($refill_number)
	{
		$this->refill_number = $refill_number;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefillDate()
	{
		return $this->refill_date;
	}
	
	/**
	 * @param mixed $refill_date
	 *
	 * @return Prescription
	 */
	public function setRefillDate($refill_date)
	{
		$this->refill_date = $refill_date;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefilledOff()
	{
		return $this->refilled_off;
	}
	
	/**
	 * @param mixed $refilled_off
	 *
	 * @return Prescription
	 */
	public function setRefilledOff($refilled_off)
	{
		$this->refilled_off = $refilled_off;
		return $this;
	}
	
	public function getServiceCentre()
	{
		return $this->serviceCentre;
	}
	
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	/**
	 * @return mixed
	 */
	public function getEncounter()
	{
		return $this->encounter;
	}
	
	/**
	 * @param mixed $encounter
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
	}
	
	/**
	 * @return mixed
	 */
	public function getPrescribedBy()
	{
		return $this->prescribed_by;
	}
	
	/**
	 * @param mixed $prescribed_by
	 */
	public function setPrescribedBy($prescribed_by)
	{
		$this->prescribed_by = $prescribed_by;
	}
	
}
