<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StaffSpecialization
 *
 * @author pauldic
 */
class StaffSpecialization implements JsonSerializable
{
	private $id;
	private $code;
	private $name;
	private $hospital;
	private $inpatient;
	private $outpatient;
	
	
	function __construct($id = null)
	{
		$this->id = $id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getCode()
	{
		return $this->code;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function setCode($code)
	{
		$this->code = $code;
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * @param mixed $hospital
	 */
	public function setHospital($hospital)
	{
		$this->hospital = $hospital;
	}
	
	/**
	 * @return mixed
	 */
	public function getHospital()
	{
		return $this->hospital;
	}
	
	/**
	 * @return mixed
	 */
	public function getInpatient()
	{
		return $this->inpatient;
	}
	
	/**
	 * @param mixed $inpatient
	 *
	 * @return StaffSpecialization
	 */
	public function setInpatient($inpatient)
	{
		$this->inpatient = $inpatient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getOutpatient()
	{
		return $this->outpatient;
	}
	
	/**
	 * @param mixed $outpatient
	 *
	 * @return StaffSpecialization
	 */
	public function setOutpatient($outpatient)
	{
		$this->outpatient = $outpatient;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
}
