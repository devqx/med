<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 2:13 PM
 */
//defined in lab_requests table
class LabGroup implements JsonSerializable
{
	private $id;
	private $group_name;
	private $patient;
	private $requested_by;
	private $request_time;
	private $requestNote;
	private $preferred_specimens;
	private $clinic;
	private $referral;
	private $serviceCentre;
	private $encounter;
	private $inPatient;
//    private $labs;
	//[in]correctly used in creating a lab request
	private $request_data;
	private $urgent;

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
	 * @return LabGroup
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGroupName()
	{
		return $this->group_name;
	}

	/**
	 * @param mixed $group_name
	 * @return LabGroup
	 */
	public function setGroupName($group_name)
	{
		$this->group_name = $group_name;
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
	 * @return LabGroup
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestedBy()
	{
		return $this->requested_by;
	}

	/**
	 * @param mixed $requested_by
	 * @return LabGroup
	 */
	public function setRequestedBy($requested_by)
	{
		$this->requested_by = $requested_by;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestTime()
	{
		return $this->request_time;
	}

	/**
	 * @param mixed $request_time
	 * @return LabGroup
	 */
	public function setRequestTime($request_time)
	{
		$this->request_time = $request_time;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestNote()
	{
		return $this->requestNote;
	}

	/**
	 * @param mixed $requestNote
	 * @return LabGroup
	 */
	public function setRequestNote($requestNote)
	{
		$this->requestNote = $requestNote;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPreferredSpecimens()
	{
		return $this->preferred_specimens;
	}

	/**
	 * @param mixed $preferred_specimens
	 * @return LabGroup
	 */
	public function setPreferredSpecimens($preferred_specimens)
	{
		$this->preferred_specimens = $preferred_specimens;
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
	 * @return LabGroup
	 */
	public function setClinic($clinic)
	{
		$this->clinic = $clinic;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReferral()
	{
		return $this->referral;
	}

	/**
	 * @param mixed $referral
	 * @return LabGroup
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
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
	 * @return LabGroup
	 */
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInPatient()
	{
		return $this->inPatient;
	}

	/**
	 * @param mixed $inPatient
	 * @return LabGroup
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestData()
	{
		return $this->request_data;
	}

	/**
	 * @param mixed $request_data
	 * @return LabGroup
	 */
	public function setRequestData($request_data)
	{
		$this->request_data = $request_data;
		return $this;
	}


	/**
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 */
	public function __toString()
	{
		return $this->group_name;
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
	 * @return LabGroup
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUrgent()
	{
		return $this->urgent;
	}
	
	/**
	 * @param mixed $urgent
	 *
	 * @return LabGroup
	 */
	public function setUrgent($urgent)
	{
		$this->urgent = $urgent;
		return $this;
	}
	
} 