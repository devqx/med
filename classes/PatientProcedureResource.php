<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 4:00 PM
 */
class PatientProcedureResource implements JsonSerializable
{
	private $id;
	private $patient_procedure;
	private $resource;
	private $resourceType;
	private $creator;
	private $create_time;
	
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
	 * @return PatientProcedureResource
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPatientProcedure()
	{
		return $this->patient_procedure;
	}
	
	/**
	 * @param mixed $patient_procedure
	 *
	 * @return PatientProcedureResource
	 */
	public function setPatientProcedure($patient_procedure)
	{
		$this->patient_procedure = $patient_procedure;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getResource()
	{
		return $this->resource;
	}
	
	/**
	 * @param mixed $resource
	 *
	 * @return PatientProcedureResource
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getResourceType()
	{
		return $this->resourceType;
	}
	
	/**
	 * @param mixed $resourceType
	 *
	 * @return PatientProcedureResource
	 */
	public function setResourceType($resourceType)
	{
		$this->resourceType = $resourceType;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreator()
	{
		return $this->creator;
	}
	
	/**
	 * @param mixed $creator
	 *
	 * @return PatientProcedureResource
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreateTime()
	{
		return $this->create_time;
	}
	
	/**
	 * @param mixed $create_time
	 *
	 * @return PatientProcedureResource
	 */
	public function setCreateTime($create_time)
	{
		$this->create_time = $create_time;
		return $this;
	}
	
	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 */
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
} 