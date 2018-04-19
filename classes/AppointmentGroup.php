<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppointmentGroup
 *
 * @author pauldic
 */
class AppointmentGroup implements JsonSerializable
{
	private $id;
	private $createTime;
	private $creator;
	private $type;
	private $clinic;
	private $resource;
	private $description;
	private $department;
	private $patient;
	private $appointments;
	private $invitees;
	private $isAllDay;


	function __construct($id = null)
	{
		$this->id = $id;
		date_default_timezone_set('Africa/Lagos');
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
	 * @return AppointmentGroup
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreateTime()
	{
		return $this->createTime;
	}

	/**
	 * @param mixed $createTime
	 * @return AppointmentGroup
	 */
	public function setCreateTime($createTime)
	{
		$this->createTime = $createTime;
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
	 * @return AppointmentGroup
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
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
	 * @return AppointmentGroup
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinic()
	{
		return $this->clinic;
	}

	/**
	 * @param mixed $clinic
	 * @return AppointmentGroup
	 */
	public function setClinic($clinic)
	{
		$this->clinic = $clinic;
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
	 * @return AppointmentGroup
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
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
	 * @return AppointmentGroup
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDepartment()
	{
		return $this->department;
	}

	/**
	 * @param mixed $department
	 * @return AppointmentGroup
	 */
	public function setDepartment($department)
	{
		$this->department = $department;
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
	 * @return AppointmentGroup
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAppointments()
	{
		return $this->appointments;
	}

	/**
	 * @param mixed $appointments
	 * @return AppointmentGroup
	 */
	public function setAppointments($appointments)
	{
		$this->appointments = $appointments;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInvitees()
	{
		return $this->invitees;
	}

	/**
	 * @param mixed $invitees
	 * @return AppointmentGroup
	 */
	public function setInvitees($invitees)
	{
		$this->invitees = $invitees;
		return $this;
	}

	public function isAllDay()
	{
		return (bool)$this->isAllDay;
	}

	public function setIsAllDay($isAllDay)
	{
		$this->isAllDay = $isAllDay;
		return $this;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}


}
