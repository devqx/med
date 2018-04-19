<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 12:56 PM
 */
class PatientLab implements JsonSerializable
{
	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 *       which is a value of any type other than a resource.
	 */
	private $id;
	private $patient;
	private $test;
	private $lab_group;
	private $value;
	private $resultApproved;
	private $approveDate;
	private $approver;
	private $performed_by;
	private $notes;
	private $specimen;
	private $specimen_collected_by;
	private $specimen_note;
	private $specimen_date;
	
	private $labResult;
	private $test_date;
	
	private $status;
	private $serviceCentre;
	private $received;
	private $receivedBy;
	
	private $bill;
	
	function __construct($id = null)
	{
		$this->id = $id;
	}
	
	/**
	 * @return mixed
	 */
	public function getReceived()
	{
		return $this->received;
	}
	
	/**
	 * @param mixed $received
	 */
	public function setReceived($received)
	{
		$this->received = $received;
	}
	
	/**
	 * @return mixed
	 */
	public function getReceivedBy()
	{
		return $this->receivedBy;
	}
	
	/**
	 * @param mixed $receivedBy
	 */
	public function setReceivedBy($receivedBy)
	{
		$this->receivedBy = $receivedBy;
	}
	
	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @param mixed $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecimenDate()
	{
		return $this->specimen_date;
	}
	
	/**
	 * @param mixed $specimen_date
	 */
	public function setSpecimenDate($specimen_date)
	{
		$this->specimen_date = $specimen_date;
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
	public function getLabGroup()
	{
		return $this->lab_group;
	}
	
	/**
	 * @param mixed $lab_group
	 */
	public function setLabGroup($lab_group)
	{
		$this->lab_group = $lab_group;
	}
	
	/**
	 * @return mixed
	 */
	public function getNotes()
	{
		return $this->notes;
	}
	
	/**
	 * @param mixed $notes
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
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
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
	}
	
	/**
	 * @return mixed
	 */
	public function getPerformedBy()
	{
		return $this->performed_by;
	}
	
	/**
	 * @param mixed $performed_by
	 */
	public function setPerformedBy($performed_by)
	{
		$this->performed_by = $performed_by;
	}
	
	/**
	 * @param mixed $specimen
	 */
	public function setSpecimens($specimen)
	{
		$this->specimen = $specimen;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecimens()
	{
		return $this->specimen;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecimenCollectedBy()
	{
		return $this->specimen_collected_by;
	}
	
	/**
	 * @param mixed $specimen_collected_by
	 */
	public function setSpecimenCollectedBy($specimen_collected_by)
	{
		$this->specimen_collected_by = $specimen_collected_by;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecimenNote()
	{
		return $this->specimen_note;
	}
	
	/**
	 * @param mixed $specimen_note
	 */
	public function setSpecimenNote($specimen_note)
	{
		$this->specimen_note = $specimen_note;
	}
	
	/**
	 * @return mixed
	 */
	public function getTest()
	{
		return $this->test;
	}
	
	/**
	 * @param mixed $test
	 */
	public function setTest($test)
	{
		$this->test = $test;
	}
	
	/**
	 * @return mixed
	 */
	public function getTestDate()
	{
		return $this->test_date;
	}
	
	/**
	 * @param mixed $test_date
	 */
	public function setTestDate($test_date)
	{
		$this->test_date = $test_date;
	}
	
	/**
	 * @param mixed $resultApproved
	 */
	public function setResultApproved($resultApproved)
	{
		$this->resultApproved = $resultApproved;
	}
	
	/**
	 * @return mixed
	 */
	public function isResultApproved()
	{
		return $this->resultApproved;
	}
	
	/**
	 * @return mixed
	 */
	public function getApproveDate()
	{
		return $this->approveDate;
	}
	
	/**
	 * @param mixed $approveDate
	 */
	public function setApproveDate($approveDate)
	{
		$this->approveDate = $approveDate;
	}
	
	/**
	 * @return mixed
	 */
	public function getApprover()
	{
		return $this->approver;
	}
	
	/**
	 * @param mixed $approver
	 */
	public function setApprover($approver)
	{
		$this->approver = $approver;
	}
	
	function getLabResult()
	{
		return $this->labResult;
	}
	
	function setLabResult($labResult)
	{
		$this->labResult = $labResult;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	public function getServiceCentre()
	{
		return $this->serviceCentre;
	}
	
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
	}
	
	/**
	 * @return mixed
	 */
	public function getBill()
	{
		return $this->bill;
	}
	
	/**
	 * @param mixed $bill
	 *
	 * @return PatientLab
	 */
	public function setBill($bill)
	{
		$this->bill = $bill;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
} 